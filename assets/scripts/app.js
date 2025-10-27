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
            fetch('/admin/credential/toggle-group/' + groupId + '/' + (groupCheckbox.checked ? 1 : 0), { method: 'post' })
                .then((response) => {
                    if (response.status === 200) {
                        let checkboxes = document.querySelectorAll('.lle-credential-checkbox-group-' + groupId + '-credential');
                        checkboxes.forEach((checkbox) => {
                            checkbox.checked = groupCheckbox.checked;
                        });

                        let sectionCheckboxes = document.querySelectorAll('.lle-credential-checkbox-group-' + groupId + '-section');
                        sectionCheckboxes.forEach((sectionCheckbox) => {
                            sectionCheckbox.checked = groupCheckbox.checked;
                        });

                        showToast("Success", "#1CC88A");
                    } else {
                        groupCheckbox.checked = !groupCheckbox.checked;

                        showToast("Error", "#E74A3B");
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
            fetch('/admin/credential/toggle-section/' + sectionName + '/' + groupId + '/' + (sectionCheckbox.checked ? 1 : 0), { method: 'post' })
                .then((response) => {
                    if (response.status === 200) {
                        let checkboxes = document.querySelectorAll('.lle-credential-checkbox-group-' + groupId + '-section-' + sectionName + '-credential');
                        checkboxes.forEach((checkbox) => {
                            checkbox.checked = sectionCheckbox.checked;
                        });

                        showToast("Success", "#1CC88A");
                    } else {
                        sectionCheckbox.checked = !sectionCheckbox.checked;

                        showToast("Error", "#E74A3B");
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
            fetch('/admin/credential/toggle-credential/' + credentialId + '/' + groupId + '/' + (credentialCheckbox.checked ? 1 : 0), { method: 'post' })
                .then((response) => {
                    if (response.status === 200) {
                        showToast("Success", "#1CC88A");
                    } else {
                        credentialCheckbox.checked = !credentialCheckbox.checked;

                        showToast("Error", "#E74A3B");
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
            fetch('/admin/credential/allowed-status/' + credentialId + '/' + groupId + '/' + (credentialByStatus.checked ? 1 : 0), { method: 'post' })
                .then((response) => {
                    if (response.status === 200) {
                        let statusList = document.querySelector('.lle-credential-group-' + groupId + '-credential-' + credentialId + '-show-status');
                        if (statusList.classList.contains('d-none')) {
                            statusList.classList.remove('d-none');
                        } else {
                            statusList.classList.add('d-none');
                        }

                        showToast("Success", "#1CC88A");
                    } else {
                        credentialByStatus.checked = !credentialByStatus.checked;

                        showToast("Error", "#E74A3B");
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
            fetch('/admin/credential/allowed-by-status/' + credentialId + '/' + groupId + '/' + credentialStatus + '/' + (credentialStatusCheckbox.checked ? 1 : 0), { method: 'post' })
                .then((response) => {
                    if (response.status === 200) {
                        showToast("Success", "#1CC88A");
                    } else {
                        credentialStatusCheckbox.checked = !credentialStatusCheckbox.checked;

                        showToast("Error", "#E74A3B");
                    }
                });
        });
    });
}

function showToast(text, color) {
    Toastify({
        text: text,
        duration: 1500,
        style: {
            background: color,
            padding: '15px 20px',
            fontSize: '17px',
        },
    }).showToast();
}
