window.addEventListener('load', () => {
    let matrice = document.getElementById('matrice');
    if (matrice) {
        checkRubriques();
        checkGroups();

        checkAllCredentialsOfGroup();
        checkAllCredentialsOfRubrique();
        checkCredential();
        enableCredentialByStatus();
        checkCredentialByStatus();
    }
});

function checkRubriques() {
    const rubriqueCheckboxes = document.querySelectorAll('.lle-credential-checkbox-group-rubrique');

    // Check rubriqueCheckbox if all credentials are checked
    for (let rubriqueCheckbox of rubriqueCheckboxes) {
        let groupId = rubriqueCheckbox.dataset.groupId;
        let rubriqueName = rubriqueCheckbox.dataset.rubriqueName;

        let allChecked = true;
        let checkboxes = document.querySelectorAll('.lle-credential-checkbox-group-' + groupId + '-rubrique-' + rubriqueName + '-credential');

        for (let checkbox of checkboxes) {
            if (!checkbox.checked) {
                allChecked = false;
                break;
            }
        }

        rubriqueCheckbox.checked = allChecked;
    }
}

function checkGroups() {
    const groupCheckboxes = document.querySelectorAll('.lle-credential-checkbox-group');

    // Check groupCheckbox if all rubriques are checked
    for (let groupCheckbox of groupCheckboxes) {
        let groupId = groupCheckbox.dataset.groupId;

        let allChecked = true;
        let checkboxes = document.querySelectorAll('.lle-credential-checkbox-group-' + groupId + '-rubrique');

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
            fetch('/project/credential/toggle-group/' + groupId + '/' + (groupCheckbox.checked ? 1 : 0), { method: 'post' })
                .then((response) => {
                    if (response.status === 200) {
                        let checkboxes = document.querySelectorAll('.lle-credential-checkbox-group-' + groupId + '-credential');
                        checkboxes.forEach((checkbox) => {
                            checkbox.checked = groupCheckbox.checked;
                        });

                        let rubriqueCheckboxes = document.querySelectorAll('.lle-credential-checkbox-group-' + groupId + '-rubrique');
                        rubriqueCheckboxes.forEach((rubriqueCheckbox) => {
                            rubriqueCheckbox.checked = groupCheckbox.checked;
                        });
                    } else {
                        groupCheckbox.checked = !groupCheckbox.checked;
                    }
                });
        });
    });
}

function checkAllCredentialsOfRubrique() {
    const rubriqueCheckboxes = document.querySelectorAll('.lle-credential-checkbox-group-rubrique');
    rubriqueCheckboxes.forEach((rubriqueCheckbox) => {
        rubriqueCheckbox.addEventListener('click', () => {
            let shouldCheck = window.confirm(rubriqueCheckbox.dataset.confirmMessage);
            if (!shouldCheck) {
                rubriqueCheckbox.checked = !rubriqueCheckbox.checked;
                return;
            }

            let groupId = rubriqueCheckbox.dataset.groupId;
            let rubriqueName = rubriqueCheckbox.dataset.rubriqueName;
            fetch('/project/credential/toggle-rubrique/' + rubriqueName + '/' + groupId + '/' + (rubriqueCheckbox.checked ? 1 : 0), { method: 'post' })
                .then((response) => {
                    if (response.status === 200) {
                        let checkboxes = document.querySelectorAll('.lle-credential-checkbox-group-' + groupId + '-rubrique-' + rubriqueName + '-credential');
                        checkboxes.forEach((checkbox) => {
                            checkbox.checked = rubriqueCheckbox.checked;
                        });
                    } else {
                        rubriqueCheckbox.checked = !rubriqueCheckbox.checked;
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
            fetch('/project/credential/toggle-credential/' + credentialId + '/' + groupId + '/' + (credentialCheckbox.checked ? 1 : 0), { method: 'post' })
                .then((response) => {
                    if (response.status !== 200) {
                        credentialCheckbox.checked = !credentialCheckbox.checked;
                    }
                });

            checkRubriques();
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
            fetch('/project/credential/allowed-status/' + credentialId + '/' + groupId + '/' + (credentialByStatus.checked ? 1 : 0), { method: 'post' })
                .then((response) => {
                    if (response.status === 200) {
                        let statusList = document.querySelector('.lle-credential-group-' + groupId + '-credential-' + credentialId + '-show-status');
                        if (statusList.classList.contains('d-none')) {
                            statusList.classList.remove('d-none');
                        } else {
                            statusList.classList.add('d-none');
                        }
                    } else {
                        credentialByStatus.checked = !credentialByStatus.checked;
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
            fetch('/project/credential/allowed-by-status/' + credentialId + '/' + groupId + '/' + credentialStatus + '/' + (credentialStatusCheckbox.checked ? 1 : 0), { method: 'post' })
                .then((response) => {
                    if (response.status !== 200) {
                        credentialStatusCheckbox.checked = !credentialStatusCheckbox.checked;
                    }
                });
        });
    });
}
