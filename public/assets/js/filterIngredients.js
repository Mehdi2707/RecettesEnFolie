$(document).ready(function() {
    var ingredientInput = $('#filter-ingredients');
    var urlSearch = $('#ingredientsElem').data('url');
    var ingredientDropdown = $('<ul class="ingredient-dropdown list-group"></ul>');
    ingredientInput.after(ingredientDropdown);
    var ingredientsArray = [];

    // Fonction pour mettre à jour le tableau lors du chargement de la page
    function updateTableOnPageLoad() {
        var hiddenField = $('.hidden-ingredients-field');
        var ingredientsString = hiddenField.val();

        if (ingredientsString) {
            var ingredients = ingredientsString.split(',');

            // Ajouter chaque ingrédient au tableau
            ingredients.forEach(function(ingredient) {
                addIngredientToTable(ingredient);
            });
        }
    }

    // Appeler la fonction pour mettre à jour le tableau lors du chargement de la page
    updateTableOnPageLoad();

    ingredientInput.on('input', function() {
        var input = $(this).val();

        if (input == '') {
            ingredientDropdown.empty();
            return;
        }

        // Effectuer une requête AJAX pour récupérer les correspondances depuis le serveur
        $.ajax({
            url: urlSearch, // Endpoint pour la recherche des ingrédients
            method: 'GET',
            data: { search: input },
            success: function(response) {
                ingredientDropdown.empty();

                // Ajouter les correspondances dans la liste déroulante
                response.ingredients.forEach(function(ingredient) {
                    var listItem = $('<li class="ingredient-item list-group-item btn-link" style="cursor: pointer;"></li>').text(ingredient.name);
                    listItem.on('click', function() {
                        addIngredientToTable(ingredient.name);
                        ingredientInput.val('');
                        ingredientDropdown.empty();
                        ingredientInput.focus(); // Remettre le focus sur l'input
                    });
                    ingredientDropdown.append(listItem);
                });

                // Afficher ou masquer la liste des ingrédients existants
                if (ingredientDropdown.children().length > 0) {
                    ingredientDropdown.show();
                } else {
                    ingredientDropdown.hide();
                }
            },
            error: function() {
                console.log('Une erreur s\'est produite lors de la recherche des ingrédients.');
            }
        });
    });

    // Fonction pour ajouter l'ingrédient sélectionné au tableau
    function addIngredientToTable(ingredient) {
        var table = $('.ingredient-table');

        // Vérifier si l'ingrédient est déjà présent dans le tableau
        if (table.find('td').filter(function() {
            return $(this).text() === ingredient;
        }).length > 0) {
            return; // Ne rien faire si l'ingrédient est déjà présent
        }

        var row = $('<tr></tr>');
        var cell = $('<td></td>').text(ingredient);
        var deleteButton = $('<button class="btn btn-sm btn-danger m-2" type="button">Supprimer</button>');

        deleteButton.on('click', function() {
            row.remove();
            removeFromIngredientsArray(ingredient);
        });

        row.append(cell);
        row.append(deleteButton);
        table.append(row);

        addToIngredientsArray(ingredient);
    }

    // Fonction pour ajouter l'ingrédient au tableau caché
    function addToIngredientsArray(ingredient) {
        ingredientsArray.push(ingredient);
        updateHiddenIngredientsField(); // Mettre à jour le champ caché
    }

    // Fonction pour supprimer l'ingrédient du tableau caché
    function removeFromIngredientsArray(ingredient) {
        var index = ingredientsArray.indexOf(ingredient);
        if (index > -1) {
            ingredientsArray.splice(index, 1);
            updateHiddenIngredientsField(); // Mettre à jour le champ caché
        }
    }

    // Fonction pour mettre à jour le champ caché avec les valeurs du tableau d'ingrédients
    function updateHiddenIngredientsField() {
        var hiddenField = $('.hidden-ingredients-field');
        hiddenField.val(ingredientsArray.join(','));
    }

    $(document).on('click', function(event) {
        var target = $(event.target);

        // Vérifier si l'événement de clic provient de l'élément de saisie d'ingrédient ou de la liste déroulante
        if (!target.is(ingredientInput) && !target.closest(ingredientDropdown).length) {
            // Cacher la liste déroulante
            ingredientDropdown.hide();
        }
    });
});
