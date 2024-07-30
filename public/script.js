var roleElement = document.getElementById('roles');
var roles = roleElement ? roleElement.innerText : '';
var dataTableUsers ="";
$(document).ready(function() {
  // Initialisation de DataTables de contact 
    dataTables = $('#contactsTable').DataTable({
        language: {
            search: "Recherche:",
            lengthMenu: "Afficher _MENU_ enregistrements par page",
            info: "Affichage de _START_ à _END_ sur _TOTAL_ enregistrements",
            infoEmpty: "Affichage de 0 à 0 sur 0 enregistrements",
            infoFiltered: "(filtré de _MAX_ enregistrements au total)",
            loadingRecords: "Chargement...",
            zeroRecords: "Aucun enregistrement correspondant trouvé",
            emptyTable: "Aucune donnée disponible dans le tableau",
            paginate: {
                first: "Premier",
                previous: "Précédent",
                next: "Suivant",
                last: "Dernier"
            },
            aria: {
                sortAscending: ": activer pour trier la colonne par ordre croissant",
                sortDescending: ": activer pour trier la colonne par ordre décroissant"
            }
        },
        columns: [
            { title: "ID" },
            { title: "Nom" },
            { title: "Prénom" },
            { title: "Téléphone" },
            { title: "Email" },
            { title: "Adresse" },
            { title: "Catégorie" },
            { title: "Actions" }
        ]
    });
  // Initialisation de DataTables de Utilisateurs 
  dataTableUsers = $('#usersTable').DataTable({
    language: {
        search: "Recherche:",
        lengthMenu: "Afficher _MENU_ enregistrements par page",
        info: "Affichage de _START_ à _END_ sur _TOTAL_ enregistrements",
        infoEmpty: "Affichage de 0 à 0 sur 0 enregistrements",
        infoFiltered: "(filtré de _MAX_ enregistrements au total)",
        loadingRecords: "Chargement...",
        zeroRecords: "Aucun enregistrement correspondant trouvé",
        emptyTable: "Aucune donnée disponible dans le tableau",
        paginate: {
            first: "Premier",
            previous: "Précédent",
            next: "Suivant",
            last: "Dernier"
        },
        aria: {
            sortAscending: ": activer pour trier la colonne par ordre croissant",
            sortDescending: ": activer pour trier la colonne par ordre décroissant"
        }
    },
    columns: [
        { title: "ID" },
        { title: "Image" },
        { title: "Nom" },
        { title: "Prénom" },
        { title: "Email" },
        { title: "Téléphone" },
        { title: "Actions" }
    ]
});



  // AJOUTER UN CONTACT
    $("#create").on("click", function (e) {
        let formOrder = $("#formAddOrder");
        if (formOrder[0].checkValidity()) {
            e.preventDefault();
            $.ajax({
                url: window.location.origin + "/contact/add",
                type: "post",
                data: formOrder.serialize() + "&action=create",
                beforeSend: function () {
                    $('#create').prop('disabled', true);
                    $('#addSpinner').show();
                    $('#iconAdd').hide();
                },
                success: function (response) {
                    $('#create').prop('disabled', false);
                    $('#addSpinner').hide();
                    $('#iconAdd').show();
                    switch (response.status) {
                        case "success":
                            Swal.fire({
                                position: "center",
                                icon: "success",
                                title: "Contact ajouté avec succès!",
                                text: response.message,
                                showConfirmButton: false,
                                timer: 1500
                            });
                            $("#addContactModal").modal("hide");
                            formOrder[0].reset();
                            // addContactsTable(response.contact);
                            addContactsTable(response.contact);
                            break;
                        case "violation":
                            // Afficher les erreurs de validation dans le modal
                            let errorMessage = "Erreur de validation du formulaire:<br><ul>";
                            $.each(response.errors, function (key, value) {
                                errorMessage += "<li>" + key + ": " + value + "</li>";
                            });
                            errorMessage += "</ul>";
                            Swal.fire({
                                position: "center",
                                icon: "error",
                                title: "Erreur!",
                                html: errorMessage,
                                showConfirmButton: true,
                            });
                            break;
                        default:
                            Swal.fire({
                                position: "center",
                                icon: "error",
                                title: "Erreur!",
                                text: response.message,
                                showConfirmButton: false,
                                timer: 1500
                            });
                    }
                    
                },
                error: function (textStatus, errorThrown) {
                    console.log(textStatus, errorThrown);
                    Swal.fire({
                        position: "center",
                        icon: "error",
                        title: "Erreur!",
                        text: response.message,
                        showConfirmButton: true,
                    });
                    $('#create').prop('disabled', false);
                    $('#addSpinner').hide();
                    $('#iconAdd').show();
                },
            });
        } else {
            Swal.fire({
                position: "center",
                icon: "error",
                title: "Erreur!",
                text: "Veuillez remplir tous les champs requis. OU une violation de Contraintes ",
                showConfirmButton: false,
                timer: 1500
            });
        }
    });


  // Bouton de modification pour afficher le modal
    $('body').on('click', '.editBtn', function(e) {
        e.preventDefault();
        var contactId = $('.idView').text();
        $('#ViewContactModal').modal('hide');
        $.ajax({
            url: window.location.origin + '/contact/get',
            type: 'post',
            data: { id: contactId },
            success: function(response) {
                if (response.status === 'success') {
                    $('#idUpdate').val(response.contact.id);
                    $('#nomUpdate').val(response.contact.nom);
                    $('#prenomUpdate').val(response.contact.prenom);
                    $('#emailUpdate').val(response.contact.email);
                    $('#telephoneUpdate').val(response.contact.telephone);
                    $('#addressUpdate').val(response.contact.address);
                    $('#categorieUpdate').val(response.contact.categorie.id);
                    $('#UpdateContactModal').modal('show');
                } else {
                    console.error('Error: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error: ' + status + ' - ' + error);
            }
        });
    });

  // Retour au modal de vue
    $('body').on('click', '.retour', function(e) {
        $('#UpdateContactModal').modal('hide');
        $('#ViewContactModal').modal('show');
    });

  // Mise à jour d'un contact
    $('body').on('click', '.update', function(e) {
        e.preventDefault();
        // alert('Contact');
        var contactId = $('#idUpdate').val();
        let formOrder = $("#formUpdateOrder");

        if (formOrder[0].checkValidity()) {
            var formData = formOrder.serialize() + '&id=' + contactId + '&action=update';

            $.ajax({
                url: window.location.origin + "/contact/edit",
                type: "post",
                beforeSend: function() {
                // Désactiver le bouton et afficher le spinner
                $('#update').prop('disabled', true);
                $('#updateSpinner').show();
                $('#iconSync').hide();
            },
                data: formData,
                success: function (response) {
                    // Réactiver le bouton et masquer le spinner
                    $('#update').prop('disabled', false);
                    $('#updateSpinner').hide();
                    $('#iconSync').show();
                    switch (response.status) {
                        case "success":
                            $("#UpdateContactModal").modal("hide");
                            updateContactsTable(response.contact);
                            Swal.fire({
                                position: "center",
                                icon: "success",
                                title: "Contact modifié avec succès!",
                                text: response.message,
                                showConfirmButton: false,
                                timer: 1500
                            });
                            $('.idView').text(response.contact.id);
                            $('.nomView').text(response.contact.nom);
                            $('.prenomView').text(response.contact.prenom);
                            $('.emailView').text(response.contact.email);
                            $('.telephoneView').text(response.contact.telephone);
                            $('.addressView').text(response.contact.address);
                            $('.categorieView').text(response.contact.categorie.nom);
                            $('#ViewContactModal').modal('show');
                            break;
                        default:
                            Swal.fire({
                                position: "center",
                                icon: "error",
                                title: "Erreur!",
                                text: response.message,
                                showConfirmButton: false,
                                timer: 1500
                            });
                    }
                },
                error: function (xhr, status, error) {
                    // Réactiver le bouton et masquer le spupdateSpinnerinner en cas d'erreur
                    $('#update').prop('disabled', false);
                    $('#updateSpinner').hide();
                    $('#iconSync').show();

                    console.error('AJAX Error: ' + status + ' - ' + error);
                }
            });
        } else {
            alert("Veuillez remplir tous les champs requis.");
        }
    });

  // Contactez-Nous
    $('body').on('click', '.contactNous', function(e) {
        e.preventDefault();
        let formOrder = $("#formNousContactezOrder");

        if (formOrder[0].checkValidity()) {
            var formData = formOrder.serialize() + '&action=sendEmail';

            $.ajax({
                url: window.location.origin + "/contact/sendEmail",
                type: "post",
                beforeSend: function() {
                // Désactiver le bouton et afficher le spinner
                $('#contactNous').prop('disabled', true);
                $('#contactNousSpinner').show();
                $('#iconPhone').hide();
            },
                data: formData,
                success: function (response) {
                    // Réactiver le bouton et masquer le spinner
                    $('#contactNous').prop('disabled', false);
                    $('#contactNousSpinner').hide();
                    $('#iconPhone').show();
                    switch (response.status) {
                        case "success":
                            $("#NousContactezModal").modal("hide");
                            Swal.fire({
                                position: "center",
                                icon: "success",
                                title: "Demande Envoyé!",
                                text: response.message,
                                showConfirmButton: false,
                                timer: 1500
                            });
                            break;
                        case "violation":
                            // Afficher les erreurs de validation dans le modal
                            let errorMessage = "Erreur de validation du formulaire:<br><ul>";
                            $.each(response.errors, function (key, value) {
                                errorMessage += "<li>" + key + ": " + value + "</li>";
                            });
                            errorMessage += "</ul>";
                            Swal.fire({
                                position: "center",
                                icon: "error",
                                title: "Erreur!",
                                html: errorMessage,
                                showConfirmButton: true,
                            });
                            break;
                        default:
                            Swal.fire({
                                position: "center",
                                icon: "error",
                                title: "Erreur!",
                                text: response.message,
                                showConfirmButton: false,
                                timer: 1500
                            });
                    }
                },
                error: function (xhr, status, error) {
                    // Réactiver le bouton et masquer le spupdateSpinnerinner en cas d'erreur
                    $('#contactNous').prop('disabled', false);
                    $('#contactNousSpinner').hide();
                    $('#iconPhone').show();

                    console.error('AJAX Error: ' + status + ' - ' + error);
                }
            });
        } else {
            alert("Veuillez remplir tous les champs requis.");
        }
    });
 
    // Suppression d'un contact
    $('body').on('click', '.deleteBtn', function(e) {
    e.preventDefault();
    var contactId = $(this).data('id'); // Supposons que le bouton de suppression a un attribut data-id avec l'ID du contact
    const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
            confirmButton: "btn btn-success ", // Ajoute une marge à droite
            cancelButton: "btn btn-danger me-3"
        },
        buttonsStyling: false
    });

    swalWithBootstrapButtons.fire({
        title: "Êtes-vous sûr ?",
        text: "Vous ne pourrez pas revenir en arrière !",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Oui, supprimer !",
        cancelButtonText: "Non, annuler !",
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Envoyer une requête AJAX pour supprimer le contact
            $.ajax({
                url: window.location.origin + "/contact/delete/" + contactId,
                type: "GET",
                success: function(response) {
                    if (response.status === "success") {
                        swalWithBootstrapButtons.fire({
                            title: "Supprimé !",
                            text: "Votre contact a été supprimé.",
                            icon: "success"
                        });
                        // Mettre à jour la table des contacts
                        deleteContactsTable(contactId); // Supposons que cette fonction met à jour la table
                    } else {
                        swalWithBootstrapButtons.fire({
                            title: "Erreur !",
                            text: response.message,
                            icon: "error"
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Erreur AJAX : ' + status + ' - ' + error);
                    swalWithBootstrapButtons.fire({
                        title: "Erreur !",
                        text: "Une erreur s'est produite lors de la suppression du contact.",
                        icon: "error"
                    });
                }
            });
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            swalWithBootstrapButtons.fire({
                title: "Annulé",
                text: "Votre contact est en sécurité :)",
                icon: "error"
            });
        }
    });
    });





  // Bouton d'information
    $('body').on('click', '.infoBtn', function(e) {
        e.preventDefault();
        var contactId = $(this).closest('tr').data('id');
        $.ajax({
            url: window.location.origin + '/contact/get',
            type: 'post',
            data: { id: contactId },
            success: function(response) {
                if (response.status === 'success') {
                    $('.idView').text(response.contact.id);
                    $('.nomView').text(response.contact.nom);
                    $('.prenomView').text(response.contact.prenom);
                    $('.emailView').text(response.contact.email);
                    $('.telephoneView').text(response.contact.telephone);
                    $('.addressView').text(response.contact.address);
                    $('.categorieView').text(response.contact.categorie.nom);
                    $('#ViewContactModal').modal('show');
                } else {
                    console.error('Error: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error: ' + status + ' - ' + error);
            }
        });
    });


    function addContactsTable(contact) {
        var actions = `
            <a href="#" class="btn btn-primary btn-sm me-2 infoBtn" data-id="${contact.id}" title="Voir détails">
                <i class="fas fa-eye"></i>
            </a>`;
    
        if (roles == 'ROLE_ADMIN') {
            actions += `
            <a href="#" class="btn btn-danger btn-sm me-2 deleteBtn" data-id="${contact.id}" title="Supprimer">
                <i class="fas fa-trash-alt"></i>
            </a>`;
        }
    
        var row = dataTables.row.add([
            1,
            contact.nom,
            contact.prenom,
            contact.telephone,
            contact.email,
            contact.address,
            contact.categorie.nom,
            actions
        ]).draw().node();
    
        $(row).attr('data-id', contact.id);
    }
    
  function updateContactsTable(contact) {
    var row = dataTables.row($(`tr[data-id='${contact.id}']`));
    var actions = `
            <a href="#" class="btn btn-primary btn-sm me-2 infoBtn" data-id="${contact.id}" title="Voir détails">
                <i class="fas fa-eye"></i>
            </a>`;
    
        if (roles == 'ROLE_ADMIN') {
            actions += `
            <a href="#" class="btn btn-danger btn-sm me-2 deleteBtn" data-id="${contact.id}" title="Supprimer">
                <i class="fas fa-trash-alt"></i>
            </a>`;
        }
    row.data([
        contact.id,
        contact.nom,
        contact.prenom,
        contact.telephone,
        contact.email,
        contact.address,
        contact.categorie.nom,
        actions
    ]);
    row.draw();
  }


  function deleteContactsTable(id) {
    var row = dataTables.row($(`tr[data-id='${id}']`));
    row.remove();
    dataTables.draw();
    dataTables.api().ajax.reload();

  }

  //JAVASCRIPT UTILISATEUR 
  // Suppression d'un contact
  $('body').on('click', '.deleteUserBtn', function(e) {
    e.preventDefault();
    var UserId = $(this).data('id'); // Supposons que le bouton de suppression a un attribut data-id avec l'ID du contact
    const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
            confirmButton: "btn btn-success ", // Ajoute une marge à droite
            cancelButton: "btn btn-danger me-3"
        },
        buttonsStyling: false
    });

    swalWithBootstrapButtons.fire({
        title: "Êtes-vous sûr ?",
        text: "Vous ne pourrez pas revenir en arrière !",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Oui, Accepter !",
        cancelButtonText: "Non, Annuler !",
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Envoyer une requête AJAX pour supprimer le contact
            $.ajax({
                url: window.location.origin + "/user/delete/" + UserId ,
                type: "GET",
                success: function(response) {
                    if (response.status === "success") {
                        swalWithBootstrapButtons.fire({
                            title: "Supprimé !",
                            text: "Votre User a été supprimé.",
                            icon: "success"
                        });
                        // Mettre à jour la table des contacts
                        deleteUsersTable(UserId); // Supposons que cette fonction met à jour la table
                    } else {
                        swalWithBootstrapButtons.fire({
                            title: "Erreur !",
                            text: response.message,
                            icon: "error"
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Erreur AJAX : ' + status + ' - ' + error);
                    swalWithBootstrapButtons.fire({
                        title: "Erreur !",
                        text: "Une erreur s'est produite lors de la suppression de l'utilisateur.",
                        icon: "error"
                    });
                }
            });
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            swalWithBootstrapButtons.fire({
                title: "Annulé",
                text: "Votre contact est en sécurité :)",
                icon: "error"
            });
        }
    });
    });

    function deleteUsersTable(id) {
        var row = dataTableUsers.row($(`tr[data-id='${id}']`));
        row.remove();
        dataTableUsers.draw();
    
      }

      // Bouton de Profile
    $('#profil').on('click', function(e) {
        $('#profilUser').modal('show');
    });
      // Bouton modifier
    $('#btnUpdate').on('click', function(e) {
        $('#profilUser').modal('hide');
        $('#profilUpdateUser').modal('show');
    });
      // Retour de User
    $('#retourUser').on('click', function(e) {
        $('#profilUpdateUser').modal('hide');
        $('#profilUser').modal('show');
    });
      // Mise à jour de l'utilisateur
    $('#updateUser').on('click', function(e) {
        e.preventDefault();
        // alert('Contact');
        var UserId = document.getElementById('idUpdateUser').textContent.trim();
        let formOrder = $("#formUpdateUserOrder");

        if (formOrder[0].checkValidity()) {
            var formData = formOrder.serialize() + '&id=' + UserId + '&action=update';

            $.ajax({
                url: window.location.origin + "/user/edit/profil",
                type: "post",
                beforeSend: function() {
                // Désactiver le bouton et afficher le spinner
                $('#updateUser').prop('disabled', true);
                $('#profilUserSpinner').show();
                $('#iconEditUser').hide();
            },
                data: formData,
                success: function (response) {
                    // Réactiver le bouton et masquer le spinner
                    $('#updateUser').prop('disabled', false);
                    $('#profilUserSpinner').hide();
                    $('#iconEditUser').show();
                    switch (response.status) {
                        case "success":
                            $("#profilUpdateUser").modal("hide");
                            Swal.fire({
                                position: "center",
                                icon: "success",
                                title: "Profil modifié avec succès!",
                                text: response.message,
                                showConfirmButton: false,
                                timer: 1500
                            });
                            $('.nomUser').text(response.user.nom);
                            $('.prenomUser').text(response.user.prenom);
                            $('.telephoneUser').text(response.user.telephone);
                            $('#profilUser').modal('show');
                            break;
                        default:
                            Swal.fire({
                                position: "center",
                                icon: "error",
                                title: "Erreur!",
                                text: response.message,
                                showConfirmButton: false,
                                timer: 1500
                            });
                    }
                },
                error: function (xhr, status, error) {
                    // Réactiver le bouton et masquer le spupdateSpinnerinner en cas d'erreur
                    $('#updateUser').prop('disabled', false);
                    $('#profilUserSpinner').hide();
                    $('#iconEditUser').show();
                    alert('Une erreur ses produit');
                }
            });
        } else {
            alert("Veuillez remplir tous les champs requis.");
        }
    });



});
