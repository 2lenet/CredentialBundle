import Toastify from 'toastify-js'

window.addEventListener('load', () => {
    let matrice = document.getElementById('lle-credential-matrice');
    if (matrice) {
        checkSections();
        checkGroups();

        checkAllCredentialsOfGroup();
        checkAllCredentialsOfSection();
        checkCredential();
        enableCredentialByStatus();
        checkCredentialByStatus();
    }
});

function checkSections() {
    const sectionCheckboxes = document.querySelectorAll('.lle-credential-checkbox-group-section');

    // Check sectionCheckbox if all credentials are checked
    for (let sectionCheckbox of sectionCheckboxes) {
        let groupId = sectionCheckbox.dataset.groupId;
        let sectionName = sectionCheckbox.dataset.sectionName;

        let allChecked = true;
        let checkboxes = document.querySelectorAll('.lle-credential-checkbox-group-' + groupId + '-section-' + sectionName + '-credential');

        for (let checkbox of checkboxes) {
            if (!checkbox.checked) {
                allChecked = false;
                break;
            }
        }

        sectionCheckbox.checked = allChecked;
    }
}

function checkGroups() {
    const groupCheckboxes = document.querySelectorAll('.lle-credential-checkbox-group');

    // Check groupCheckbox if all sections are checked
    for (let groupCheckbox of groupCheckboxes) {
        let groupId = groupCheckbox.dataset.groupId;

        let allChecked = true;
        let checkboxes = document.querySelectorAll('.lle-credential-checkbox-group-' + groupId + '-section');

        for (let checkbox of checkboxes) {
            if (!checkbox.checked) {
                allChecked = false;
                break;
            }
        }

        groupCheckbox.checked = allChecked;
    }
}

const msg = window.lleCredentialMessages || {};
const GENERIC_ERROR = msg.genericError || 'An error occurred. Please contact an administrator.';

/**
 * Fetch a URL and resolve with { ok, remoteError, error }.
 * - ok: true if HTTP 2xx
 * - remoteError: string if local succeeded but remote failed
 * - error: generic message if local failed (HTTP error or network error)
 * Never rejects.
 */
async function apiCall(url) {
    try {
        const response = await fetch(url, { method: 'post' });
        const data = await response.json().catch(() => null);
        return {
            ok: response.ok,
            remoteError: data && data.remoteError ? data.remoteError : null,
            error: response.ok ? null : (data && data.error ? data.error : GENERIC_ERROR),
        };
    } catch {
        return { ok: false, remoteError: null, error: GENERIC_ERROR };
    }
}

function checkAllCredentialsOfGroup() {
    const groupCheckboxes = document.querySelectorAll('.lle-credential-checkbox-group');
    groupCheckboxes.forEach((groupCheckbox) => {
        groupCheckbox.addEventListener('click', () => {
            let shouldCheck = window.confirm(groupCheckbox.dataset.confirmMessage);
            if (!shouldCheck) {
                groupCheckbox.checked = !groupCheckbox.checked;
                return;
            }

            let groupId = groupCheckbox.dataset.groupId;
            apiCall('/admin/credential/toggle-group/' + groupId + '/' + (groupCheckbox.checked ? 1 : 0))
                .then(({ ok, remoteError, error }) => {
                    if (!ok) {
                        groupCheckbox.checked = !groupCheckbox.checked;
                        showToast(error, "danger");
                        return;
                    }

                    let checkboxes = document.querySelectorAll('.lle-credential-checkbox-group-' + groupId + '-credential');
                    checkboxes.forEach((checkbox) => { checkbox.checked = groupCheckbox.checked; });

                    let sectionCheckboxes = document.querySelectorAll('.lle-credential-checkbox-group-' + groupId + '-section');
                    sectionCheckboxes.forEach((sectionCheckbox) => { sectionCheckbox.checked = groupCheckbox.checked; });

                    if (remoteError) {
                        showToast(remoteError, "warning");
                    } else {
                        showToast(msg.toggleGroup?.success || 'Success', "success");
                    }
                });
        });
    });
}

function checkAllCredentialsOfSection() {
    const sectionCheckboxes = document.querySelectorAll('.lle-credential-checkbox-group-section');
    sectionCheckboxes.forEach((sectionCheckbox) => {
        sectionCheckbox.addEventListener('click', () => {
            let shouldCheck = window.confirm(sectionCheckbox.dataset.confirmMessage);
            if (!shouldCheck) {
                sectionCheckbox.checked = !sectionCheckbox.checked;
                return;
            }

            let groupId = sectionCheckbox.dataset.groupId;
            let sectionName = sectionCheckbox.dataset.sectionName;
            apiCall('/admin/credential/toggle-section/' + sectionName + '/' + groupId + '/' + (sectionCheckbox.checked ? 1 : 0))
                .then(({ ok, remoteError, error }) => {
                    if (!ok) {
                        sectionCheckbox.checked = !sectionCheckbox.checked;
                        showToast(error, "danger");
                        return;
                    }

                    let checkboxes = document.querySelectorAll('.lle-credential-checkbox-group-' + groupId + '-section-' + sectionName + '-credential');
                    checkboxes.forEach((checkbox) => { checkbox.checked = sectionCheckbox.checked; });

                    if (remoteError) {
                        showToast(remoteError, "warning");
                    } else {
                        showToast(msg.toggleSection?.success || 'Success', "success");
                    }
                });

            checkGroups();
        });
    });
}

function checkCredential() {
    const credentialCheckboxes = document.querySelectorAll('.lle-credential-checkbox');
    credentialCheckboxes.forEach((credentialCheckbox) => {
        credentialCheckbox.addEventListener('click', () => {
            let groupId = credentialCheckbox.dataset.groupId;
            let credentialId = credentialCheckbox.dataset.credentialId;
            apiCall('/admin/credential/toggle-credential/' + credentialId + '/' + groupId + '/' + (credentialCheckbox.checked ? 1 : 0))
                .then(({ ok, remoteError, error }) => {
                    if (!ok) {
                        credentialCheckbox.checked = !credentialCheckbox.checked;
                        showToast(error, "danger");
                        return;
                    }

                    if (remoteError) {
                        showToast(remoteError, "warning");
                    } else {
                        showToast(msg.toggleCredential?.success || 'Success', "success");
                    }
                });

            checkSections();
            checkGroups();
        });
    });
}

function enableCredentialByStatus() {
    const credentialsByStatus = document.querySelectorAll('.lle-credential-checkbox-status-list');
    credentialsByStatus.forEach((credentialByStatus) => {
        credentialByStatus.addEventListener('click', () => {
            let groupId = credentialByStatus.dataset.groupId;
            let credentialId = credentialByStatus.dataset.credentialId;
            apiCall('/admin/credential/allow-status/' + credentialId + '/' + groupId + '/' + (credentialByStatus.checked ? 1 : 0))
                .then(({ ok, remoteError, error }) => {
                    if (!ok) {
                        credentialByStatus.checked = !credentialByStatus.checked;
                        showToast(error, "danger");
                        return;
                    }

                    let statusList = document.querySelector('.lle-credential-group-' + groupId + '-credential-' + credentialId + '-show-status');
                    statusList.classList.toggle('d-none');

                    if (remoteError) {
                        showToast(remoteError, "warning");
                    } else {
                        showToast(msg.allowStatus?.success || 'Success', "success");
                    }
                });
        });
    });
}

function checkCredentialByStatus() {
    const credentialStatusCheckboxes = document.querySelectorAll('.lle-credential-checkbox-group-credential-status');
    credentialStatusCheckboxes.forEach((credentialStatusCheckbox) => {
        credentialStatusCheckbox.addEventListener('click', () => {
            let groupId = credentialStatusCheckbox.dataset.groupId;
            let credentialId = credentialStatusCheckbox.dataset.credentialId;
            let credentialStatus = credentialStatusCheckbox.dataset.credentialStatus;
            apiCall('/admin/credential/allow-for-status/' + credentialId + '/' + groupId + '/' + credentialStatus + '/' + (credentialStatusCheckbox.checked ? 1 : 0))
                .then(({ ok, remoteError, error }) => {
                    if (!ok) {
                        credentialStatusCheckbox.checked = !credentialStatusCheckbox.checked;
                        showToast(error, "danger");
                        return;
                    }

                    if (remoteError) {
                        showToast(remoteError, "warning");
                    } else {
                        showToast(msg.allowForStatus?.success || 'Success', "success");
                    }
                });
        });
    });
}

function showToast(text, type) {
    const colors = {
        success: '#1CC88A',
        warning: '#F6C23E',
        danger: '#E74A3B',
    };
    Toastify({
        text: text,
        duration: type === 'success' ? 1500 : 3000,
        style: {
            background: colors[type],
            padding: '15px 20px',
            fontSize: '17px',
        },
    }).showToast();
}