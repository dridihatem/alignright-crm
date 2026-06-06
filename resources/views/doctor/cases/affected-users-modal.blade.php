<!-- Modal -->
<div class="modal fade" id="affectedUsersModal" tabindex="-1" aria-labelledby="affectedUsersModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="affectedUsersModalLabel">{{ __('master.affected_users') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="affectedUsersList">
                    <!-- Affected users will be populated here -->
                </div>
                <div id="statusOptions" class="mt-3" style="display: none;">
                    <h6>{{ __('master.select_new_status') }}</h6>
                    <div class="list-group" id="statusButtons">
                        <!-- Status buttons will be populated here -->
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('master.close') }}</button>
            </div>
        </div>
    </div>
</div>

<script>
let currentCaseId = null;

function get_affected_users(caseId) {
    currentCaseId = caseId;
    
    // Show loading state
    $('#affectedUsersList').html('<div class="text-center"><div class="spinner-border" role="status"></div></div>');
    $('#statusOptions').hide();
    
    // Show modal
    $('#affectedUsersModal').modal('show');
    
    // Fetch affected users
    $.get(`/doctor/cases/${caseId}/affected-users`, function(response) {
        if (response.success) {
            let html = '';
            if (response.affected_users.length > 0) {
                html = '<div class="list-group">';
                response.affected_users.forEach(user => {
                    html += `
                        <div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">${user.name}</h6>
                            </div>
                            <p class="mb-1">${user.email}</p>
                        </div>
                    `;
                });
                html += '</div>';
                
                // Show status options
                $('#statusOptions').show();
                let statusHtml = '';
                response.affected_users[0].allowed_statuses.forEach(status => {
                    statusHtml += `
                        <button type="button" class="list-group-item list-group-item-action" 
                                onclick="change_status('${currentCaseId}', '${status}')">
                            ${status.charAt(0).toUpperCase() + status.slice(1).replace('_', ' ')}
                        </button>
                    `;
                });
                $('#statusButtons').html(statusHtml);
            } else {
                html = '<p class="text-center">' + '{{ __("master.no_affected_users") }}' + '</p>';
            }
            $('#affectedUsersList').html(html);
        }
    });
}

function change_status(caseId, status) {
    if(confirm('{{ __("master.are_you_sure") }}')) {
        $.post(`/doctor/cases/${caseId}/change-status/${status}`, function(response) {
            if (response.success) {
                $('#affectedUsersModal').modal('hide');
                location.reload();
            }
        });
    }
}
</script> 