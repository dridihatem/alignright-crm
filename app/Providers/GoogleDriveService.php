<?php

 namespace App\Providers;
 use App\Models\User;
 use Carbon\Carbon;
 use Google_Client;
 use Google_Service_Drive;
 use Google_Service_Drive_DriveFile;
 use Google_Service_Drive_Permission;

 class GoogleDriveService {

	public function uploadForUser($file, $user, $shareWithEmail = null, $cases_folder = null, $patient_id = null)
	{
		$client = new \Google_Client();
		$client->setAuthConfig(storage_path('app/credentials.json'));
		
		$client->setAccessType('offline');
		$client->setPrompt('consent');
		$client->setScopes([\Google_Service_Drive::DRIVE_FILE]);

		$client->setAccessToken([
			'access_token' => $user->google_access_token,
			'refresh_token' => $user->google_refresh_token,
			'expires_in' => \Carbon\Carbon::parse($user->google_token_expires_at)->diffInSeconds(now()),
			'created' => now()->subSeconds(60)->timestamp
		]);

		if ($client->isAccessTokenExpired()) {
			$client->fetchAccessTokenWithRefreshToken($user->google_refresh_token);
			$newToken = $client->getAccessToken();
			$user->google_access_token = $newToken['access_token'];
			$user->google_token_expires_at = now()->addSeconds($newToken['expires_in']);
			$user->save();
			$client->setAccessToken($newToken);
		}

		$drive = new \Google_Service_Drive($client);

		// 🗂️ Get or create folder structure
		$parentFolderId = $this->getOrCreateFolderStructure($drive, $cases_folder, $patient_id);

		// Check file size to determine upload method
		$fileSize = $file->getSize();
		$isLargeFile = $fileSize > 5 * 1024 * 1024; // Files larger than 5MB

		if ($isLargeFile) {
			return $this->uploadLargeFile($drive, $file, $parentFolderId, $shareWithEmail);
		} else {
			return $this->uploadSmallFile($drive, $file, $parentFolderId, $shareWithEmail);
		}
	}

	/**
	 * Upload large files using resumable upload
	 */
	private function uploadLargeFile($drive, $file, $parentFolderId, $shareWithEmail = null)
	{
		// Set longer timeout for large files
		set_time_limit(0); // No time limit
		ini_set('memory_limit', '1G');

		$fileMetadata = new \Google_Service_Drive_DriveFile([
			'name' => $file->getClientOriginalName(),
			'parents' => [$parentFolderId]
		]);

		// Use resumable upload for large files
		$chunkSizeBytes = 1 * 1024 * 1024; // 1MB chunks

		// Create media upload
		$request = $drive->files->create($fileMetadata);
		
		// Create media object
		$media = new \Google_Http_MediaFileUpload(
			$drive->getClient(),
			$request,
			$file->getMimeType(),
			null,
			true,
			$chunkSizeBytes
		);
		
		$media->setFileSize($file->getSize());

		// Open file handle
		$handle = fopen($file->getPathname(), 'rb');
		
		$uploadedFile = false;
		while (!$uploadedFile && !feof($handle)) {
			$chunk = fread($handle, $chunkSizeBytes);
			$uploadedFile = $media->nextChunk($chunk);
		}
		
		fclose($handle);

		if (!$uploadedFile) {
			throw new \Exception('Upload failed');
		}

		$fileId = $uploadedFile->id;

		// Set permissions
		$this->setFilePermissions($drive, $fileId, $shareWithEmail);

		return [
			'id' => $fileId,
			'webViewLink' => $uploadedFile->webViewLink,
			'webContentLink' => $uploadedFile->webContentLink
		];
	}

	/**
	 * Upload small files using simple upload
	 */
	private function uploadSmallFile($drive, $file, $parentFolderId, $shareWithEmail = null)
	{
		$fileMetadata = new \Google_Service_Drive_DriveFile([
			'name' => $file->getClientOriginalName(),
			'parents' => [$parentFolderId]
		]);

		$content = file_get_contents($file->getPathname());

		$uploadedFile = $drive->files->create($fileMetadata, [
			'data' => $content,
			'mimeType' => $file->getMimeType(),
			'uploadType' => 'multipart',
			'fields' => 'id,webViewLink,webContentLink'
		]);

		$fileId = $uploadedFile->id;

		// Set permissions
		$this->setFilePermissions($drive, $fileId, $shareWithEmail);

		return [
			'id' => $fileId,
			'webViewLink' => $uploadedFile->webViewLink,
			'webContentLink' => $uploadedFile->webContentLink
		];
	}

	/**
	 * Set file permissions
	 */
	private function setFilePermissions($drive, $fileId, $shareWithEmail = null)
	{
		$permission = new Google_Service_Drive_Permission();

		if ($shareWithEmail) {
			$permission->setType('user');
			$permission->setRole('reader');
			$permission->setEmailAddress($shareWithEmail);
		} else {
			$permission->setType('anyone');
			$permission->setRole('reader');
		}

		$drive->permissions->create($fileId, $permission);
	}

	/**
	 * Get or create folder structure for organizing files
	 * 
	 * @param Google_Service_Drive $drive
	 * @param string|null $cases_folder
	 * @param int|null $patient_id
	 * @return string The parent folder ID
	 */
	private function getOrCreateFolderStructure($drive, $cases_folder = null, $patient_id = null)
	{
		$parentFolderId = 'root'; // Start from root

		// Create Cases folder if specified
		if ($cases_folder) {
			$casesFolderId = $this->getOrCreateFolder($drive, $cases_folder, $parentFolderId);
			$parentFolderId = $casesFolderId;
		}

		// Create Patient folder if patient_id is specified
		if ($patient_id) {
			$patientFolderName = "Patient_{$patient_id}";
			$patientFolderId = $this->getOrCreateFolder($drive, $patientFolderName, $parentFolderId);
			$parentFolderId = $patientFolderId;
		}

		return $parentFolderId;
	}

	/**
	 * Get or create a folder with the given name
	 * 
	 * @param Google_Service_Drive $drive
	 * @param string $folderName
	 * @param string $parentId
	 * @return string The folder ID
	 */
	private function getOrCreateFolder($drive, $folderName, $parentId = 'root')
	{
		// First, try to find existing folder
		$query = "name = '{$folderName}' and mimeType = 'application/vnd.google-apps.folder' and '{$parentId}' in parents and trashed = false";
		
		$results = $drive->files->listFiles([
			'q' => $query,
			'fields' => 'files(id, name)'
		]);

		// If folder exists, return its ID
		if (!empty($results->getFiles())) {
			return $results->getFiles()[0]->getId();
		}

		// If folder doesn't exist, create it
		$folderMetadata = new Google_Service_Drive_DriveFile([
			'name' => $folderName,
			'mimeType' => 'application/vnd.google-apps.folder',
			'parents' => [$parentId]
		]);

		$folder = $drive->files->create($folderMetadata, [
			'fields' => 'id'
		]);

		return $folder->getId();
	}
 
 }