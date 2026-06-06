/**
 * Robust Chunked Upload System
 * Handles large file uploads with resumable functionality
 */

class ChunkedUploadManager {
    constructor(config = {}) {
        this.config = {
            chunkSize: 2 * 1024 * 1024, // 2MB default
            maxRetries: 3,
            retryDelay: 1000, // 1 second
            maxConcurrentUploads: 3,
            allowedTypes: ['jpg', 'jpeg', 'png', 'gif', 'webp', 'stl'],
            maxFileSize: 500 * 1024 * 1024, // 500MB
            baseUrl: '/doctor/chunked-upload',
            csrfToken: document.querySelector('meta[name="csrf-token"]')?.content,
            ...config
        };

        this.uploads = new Map(); // Active uploads
        this.uploadQueue = []; // Pending uploads
        this.activeUploads = 0;

        this.init();
    }

    init() {
        this.setupEventListeners();
        this.setupProgressDisplay();
    }

    setupEventListeners() {
        // File input change (prevent duplicate events)
        document.addEventListener('change', (e) => {
            if (e.target.type === 'file' && e.target.hasAttribute('data-chunked-upload')) {
                // Prevent duplicate uploads
                if (e.target.dataset.processing === 'true') {
                    return;
                }
                e.target.dataset.processing = 'true';
                
                this.handleFileSelection(e.target);
                
                // Reset processing flag after a delay
                setTimeout(() => {
                    e.target.dataset.processing = 'false';
                }, 1000);
            }
        });

        // Drag and drop
        this.setupDragAndDrop();

        // Page visibility API for pause/resume
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.pauseAllUploads();
            } else {
                this.resumeAllUploads();
            }
        });

        // Before unload warning
        window.addEventListener('beforeunload', (e) => {
            if (this.hasActiveUploads()) {
                e.preventDefault();
                e.returnValue = 'You have active uploads. Are you sure you want to leave?';
                return e.returnValue;
            }
        });
    }

    setupDragAndDrop() {
        const dropZones = document.querySelectorAll('[data-drop-zone]');
        
        dropZones.forEach(zone => {
            zone.addEventListener('dragover', (e) => {
                e.preventDefault();
                zone.classList.add('drag-over');
            });

            zone.addEventListener('dragleave', (e) => {
                e.preventDefault();
                zone.classList.remove('drag-over');
            });

            zone.addEventListener('drop', (e) => {
                e.preventDefault();
                zone.classList.remove('drag-over');
                
                const files = Array.from(e.dataTransfer.files);
                const category = zone.dataset.category;
                
                files.forEach(file => this.addFileToUpload(file, category));
            });
        });
    }

    setupProgressDisplay() {
        // Create or update progress container if it doesn't exist
        if (!document.querySelector('#upload-progress-container')) {
            const container = document.createElement('div');
            container.id = 'upload-progress-container';
            container.className = 'upload-progress-container';
            document.body.appendChild(container);
        }
    }

    async handleFileSelection(input) {
        const files = Array.from(input.files);
        const category = input.dataset.category || 'other_files';

        for (const file of files) {
            await this.addFileToUpload(file, category);
        }
    }

    async addFileToUpload(file, category) {
        // Validate file
        const validation = this.validateFile(file);
        if (!validation.valid) {
            this.showError(`File "${file.name}": ${validation.error}`);
            return;
        }

        // Create upload object
        const upload = {
            id: this.generateUploadId(),
            file: file,
            category: category,
            status: 'pending',
            progress: 0,
            sessionId: null,
            chunks: [],
            currentChunk: 0,
            retryCount: 0,
            startTime: null,
            speed: 0,
            estimatedTime: 0
        };

        this.uploads.set(upload.id, upload);
        this.uploadQueue.push(upload.id);

        // Display upload item
        this.displayUploadItem(upload);

        // Start processing queue
        this.processUploadQueue();
    }

    validateFile(file) {
        // Check file size
        if (file.size > this.config.maxFileSize) {
            return {
                valid: false,
                error: `File too large. Maximum size is ${this.formatFileSize(this.config.maxFileSize)}`
            };
        }

        // Check file type
        const extension = file.name.split('.').pop().toLowerCase();
        if (!this.config.allowedTypes.includes(extension)) {
            return {
                valid: false,
                error: `File type not allowed. Allowed types: ${this.config.allowedTypes.join(', ')}`
            };
        }

        return { valid: true };
    }

    async processUploadQueue() {
        while (this.uploadQueue.length > 0 && this.activeUploads < this.config.maxConcurrentUploads) {
            const uploadId = this.uploadQueue.shift();
            this.startUpload(uploadId);
        }
    }

    async startUpload(uploadId) {
        const upload = this.uploads.get(uploadId);
        if (!upload) return;

        this.activeUploads++;
        upload.status = 'initializing';
        upload.startTime = Date.now();

        try {
            // Initialize upload session
            const sessionData = await this.initializeUploadSession(upload);
            upload.sessionId = sessionData.session_id;
            upload.totalChunks = sessionData.total_chunks;
            upload.chunkSize = sessionData.chunk_size;
            upload.status = 'uploading';

            // Update display
            this.updateUploadDisplay(upload);

            // Start uploading chunks
            await this.uploadChunks(upload);

            // Complete upload
            await this.completeUpload(upload);

        } catch (error) {
            console.error('Upload failed:', error);
            upload.status = 'failed';
            upload.error = error.message;
            this.updateUploadDisplay(upload);
        } finally {
            this.activeUploads--;
            this.processUploadQueue(); // Start next upload
        }
    }

    async initializeUploadSession(upload) {
        const caseId = this.getCaseId();
        
        // Handle file type - browsers don't recognize STL MIME type
        let fileType = upload.file.type;
        if (!fileType || fileType === '') {
            // Guess file type from extension
            const extension = upload.file.name.split('.').pop().toLowerCase();
            switch (extension) {
                case 'stl':
                    fileType = 'model/stl';
                    break;
                case 'jpg':
                case 'jpeg':
                    fileType = 'image/jpeg';
                    break;
                case 'png':
                    fileType = 'image/png';
                    break;
                case 'gif':
                    fileType = 'image/gif';
                    break;
                case 'webp':
                    fileType = 'image/webp';
                    break;
                default:
                    fileType = 'application/octet-stream';
            }
        }

        console.log('Initializing upload session:', {
            caseId: caseId,
            fileName: upload.file.name,
            fileSize: upload.file.size,
            originalFileType: upload.file.type,
            detectedFileType: fileType,
            category: upload.category,
            chunkSize: this.config.chunkSize
        });

        const formData = new FormData();
        formData.append('case_id', caseId);
        formData.append('file_name', upload.file.name);
        formData.append('file_size', upload.file.size);
        formData.append('file_type', fileType);
        formData.append('file_category', upload.category);
        formData.append('chunk_size', this.config.chunkSize);

        const response = await this.makeRequest('POST', '/initialize', formData);
        
        if (!response.success) {
            console.error('Upload initialization failed:', {
                response: response,
                formData: Array.from(formData.entries())
            });
            console.error('Validation details:', response.details);
            console.error('Received data:', response.received_data);
            const errorDetails = response.details ? JSON.stringify(response.details) : '';
            throw new Error(`${response.error || 'Failed to initialize upload'}. Details: ${errorDetails}`);
        }

        return response;
    }

    async uploadChunks(upload) {
        const file = upload.file;
        const chunkSize = upload.chunkSize;
        let chunkNumber = 0;

        while (chunkNumber < upload.totalChunks) {
            // Check if upload was paused or cancelled
            if (upload.status === 'paused' || upload.status === 'cancelled') {
                return;
            }

            try {
                const start = chunkNumber * chunkSize;
                const end = Math.min(start + chunkSize, file.size);
                const chunk = file.slice(start, end);

                await this.uploadChunk(upload, chunkNumber, chunk);
                
                chunkNumber++;
                upload.currentChunk = chunkNumber;
                upload.progress = (chunkNumber / upload.totalChunks) * 100;

                // Update speed and estimated time
                this.updateUploadStats(upload);
                this.updateUploadDisplay(upload);

            } catch (error) {
                console.error(`Chunk ${chunkNumber} failed:`, error);
                
                upload.retryCount++;
                if (upload.retryCount >= this.config.maxRetries) {
                    throw new Error(`Chunk upload failed after ${this.config.maxRetries} retries`);
                }

                // Wait before retry
                await this.delay(this.config.retryDelay * upload.retryCount);
            }
        }
    }

    async uploadChunk(upload, chunkNumber, chunk) {
        const formData = new FormData();
        formData.append('session_id', upload.sessionId);
        formData.append('chunk_number', chunkNumber);
        formData.append('chunk_file', chunk, `chunk_${chunkNumber}`);

        const response = await this.makeRequest('POST', '/upload-chunk', formData);
        
        if (!response.success) {
            throw new Error(response.error || 'Chunk upload failed');
        }

        return response;
    }

    async completeUpload(upload) {
        const response = await this.makeRequest('POST', `/complete/${upload.sessionId}`, {});
        
        if (!response.success) {
            throw new Error(response.error || 'Failed to complete upload');
        }

        upload.status = 'completed';
        upload.progress = 100;
        upload.fileUploadId = response.file_upload_id;
        
        this.updateUploadDisplay(upload);
        this.showSuccess(`File "${upload.file.name}" uploaded successfully!`);

        return response;
    }

    async makeRequest(method, endpoint, data) {
        const url = this.config.baseUrl + endpoint;
        const options = {
            method: method,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': this.config.csrfToken
            }
        };

        if (data instanceof FormData) {
            options.body = data;
        } else if (data && method !== 'GET') {
            options.headers['Content-Type'] = 'application/json';
            options.body = JSON.stringify(data);
        }

        const response = await fetch(url, options);
        return await response.json();
    }

    displayUploadItem(upload) {
        const container = document.querySelector('#upload-progress-container');
        const item = document.createElement('div');
        item.id = `upload-${upload.id}`;
        item.className = 'upload-item';
        item.innerHTML = this.getUploadItemHTML(upload);
        
        container.appendChild(item);
    }

    updateUploadDisplay(upload) {
        const item = document.querySelector(`#upload-${upload.id}`);
        if (item) {
            item.innerHTML = this.getUploadItemHTML(upload);
        }
    }

    getUploadItemHTML(upload) {
        const progressClass = this.getProgressClass(upload.status);
        const statusText = this.getStatusText(upload);
        
        return `
            <div class="upload-item-content">
                <div class="upload-file-info">
                    <div class="file-name">${upload.file.name}</div>
                    <div class="file-details">
                        ${this.formatFileSize(upload.file.size)} • ${upload.category}
                    </div>
                </div>
                
                <div class="upload-progress">
                    <div class="progress-bar ${progressClass}">
                        <div class="progress-fill" style="width: ${upload.progress}%"></div>
                    </div>
                    <div class="progress-text">
                        <span class="status">${statusText}</span>
                        <span class="percentage">${Math.round(upload.progress)}%</span>
                    </div>
                    ${upload.status === 'uploading' ? this.getUploadStatsHTML(upload) : ''}
                </div>
                
                <div class="upload-actions">
                    ${this.getUploadActionsHTML(upload)}
                </div>
            </div>
        `;
    }

    getUploadStatsHTML(upload) {
        if (!upload.speed || !upload.estimatedTime) return '';
        
        return `
            <div class="upload-stats">
                <span class="speed">${this.formatSpeed(upload.speed)}</span>
                <span class="eta">ETA: ${this.formatTime(upload.estimatedTime)}</span>
            </div>
        `;
    }

    getUploadActionsHTML(upload) {
        switch (upload.status) {
            case 'uploading':
                return `<button class="btn-pause" onclick="chunkedUploader.pauseUpload('${upload.id}')">Pause</button>`;
            case 'paused':
                return `<button class="btn-resume" onclick="chunkedUploader.resumeUpload('${upload.id}')">Resume</button>`;
            case 'failed':
                return `<button class="btn-retry" onclick="chunkedUploader.retryUpload('${upload.id}')">Retry</button>`;
            case 'completed':
                return `<button class="btn-remove" onclick="chunkedUploader.removeUpload('${upload.id}')">Remove</button>`;
            default:
                return '';
        }
    }

    updateUploadStats(upload) {
        if (!upload.startTime) return;

        const elapsed = (Date.now() - upload.startTime) / 1000; // seconds
        const uploadedBytes = (upload.progress / 100) * upload.file.size;
        
        upload.speed = uploadedBytes / elapsed; // bytes per second
        
        const remainingBytes = upload.file.size - uploadedBytes;
        upload.estimatedTime = remainingBytes / upload.speed; // seconds
    }

    pauseUpload(uploadId) {
        const upload = this.uploads.get(uploadId);
        if (upload && upload.status === 'uploading') {
            upload.status = 'paused';
            this.updateUploadDisplay(upload);
        }
    }

    resumeUpload(uploadId) {
        const upload = this.uploads.get(uploadId);
        if (upload && upload.status === 'paused') {
            upload.status = 'uploading';
            this.updateUploadDisplay(upload);
        }
    }

    retryUpload(uploadId) {
        const upload = this.uploads.get(uploadId);
        if (upload && upload.status === 'failed') {
            upload.status = 'pending';
            upload.retryCount = 0;
            upload.progress = 0;
            upload.currentChunk = 0;
            this.uploadQueue.push(uploadId);
            this.updateUploadDisplay(upload);
            this.processUploadQueue();
        }
    }

    removeUpload(uploadId) {
        const upload = this.uploads.get(uploadId);
        if (upload) {
            // Cancel if active
            if (upload.status === 'uploading') {
                this.cancelUpload(uploadId);
            }
            
            // Remove from DOM
            const item = document.querySelector(`#upload-${uploadId}`);
            if (item) {
                item.remove();
            }
            
            // Remove from memory
            this.uploads.delete(uploadId);
        }
    }

    async cancelUpload(uploadId) {
        const upload = this.uploads.get(uploadId);
        if (upload && upload.sessionId) {
            try {
                await this.makeRequest('DELETE', `/cancel/${upload.sessionId}`, {});
            } catch (error) {
                console.error('Failed to cancel upload:', error);
            }
        }
        
        if (upload) {
            upload.status = 'cancelled';
            this.updateUploadDisplay(upload);
        }
    }

    pauseAllUploads() {
        this.uploads.forEach((upload, uploadId) => {
            if (upload.status === 'uploading') {
                this.pauseUpload(uploadId);
            }
        });
    }

    resumeAllUploads() {
        this.uploads.forEach((upload, uploadId) => {
            if (upload.status === 'paused') {
                this.resumeUpload(uploadId);
            }
        });
    }

    hasActiveUploads() {
        for (const upload of this.uploads.values()) {
            if (['uploading', 'initializing'].includes(upload.status)) {
                return true;
            }
        }
        return false;
    }

    // Utility methods
    generateUploadId() {
        return 'upload_' + Math.random().toString(36).substr(2, 9) + '_' + Date.now();
    }

    getCaseId() {
        // Get case ID from page context
        const caseId = window.caseId || 
                      document.querySelector('[data-case-id]')?.dataset.caseId ||
                      document.querySelector('meta[name="case-id"]')?.getAttribute('content');
        
        console.log('Getting case ID:', caseId);
        return caseId;
    }

    getProgressClass(status) {
        const classes = {
            'pending': 'progress-pending',
            'initializing': 'progress-initializing',
            'uploading': 'progress-uploading',
            'paused': 'progress-paused',
            'completed': 'progress-completed',
            'failed': 'progress-failed',
            'cancelled': 'progress-cancelled'
        };
        return classes[status] || 'progress-default';
    }

    getStatusText(upload) {
        const statuses = {
            'pending': 'Pending...',
            'initializing': 'Initializing...',
            'uploading': `Uploading chunk ${upload.currentChunk}/${upload.totalChunks}`,
            'paused': 'Paused',
            'completed': 'Completed',
            'failed': 'Failed',
            'cancelled': 'Cancelled'
        };
        return statuses[upload.status] || 'Unknown';
    }

    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    formatSpeed(bytesPerSecond) {
        return this.formatFileSize(bytesPerSecond) + '/s';
    }

    formatTime(seconds) {
        if (seconds < 60) return Math.round(seconds) + 's';
        if (seconds < 3600) return Math.round(seconds / 60) + 'm';
        return Math.round(seconds / 3600) + 'h';
    }

    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    showSuccess(message) {
        this.showNotification(message, 'success');
    }

    showError(message) {
        this.showNotification(message, 'error');
    }

    showNotification(message, type) {
        // Use existing notification system if available, or create simple one
        if (window.Flasher) {
            window.Flasher.success(message);
        } else {
            console.log(`${type.toUpperCase()}: ${message}`);
            alert(message); // Fallback
        }
    }
}

// Initialize the chunked uploader when the page loads
document.addEventListener('DOMContentLoaded', function() {
    window.chunkedUploader = new ChunkedUploadManager();
});
