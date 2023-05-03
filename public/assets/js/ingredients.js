// JavaScript pour gérer l'ajout dynamique d'ingrédients
var $addIngredientLink = $('#add-ingredient');
var ingredientList = $('#ingredients-list');

$addIngredientLink.on('click', function(e) {
    e.preventDefault();

    var ingredientIndex = ingredientList.children().length;
    var ingredientPrototype = ingredientList.data('prototype');

    var ingredientForm = ingredientPrototype.replace(/__name__/g, ingredientIndex);
    ingredientList.append('<li>' + ingredientForm + '</li>');


    // Réattacher l'écouteur d'événement au champ d'entrée d'ingrédient initial
    var ingredientInput = $('.ingredient-name-input');
    var ingredientDropdown = $('<ul class="ingredient-dropdown"></ul>');
    ingredientInput.after(ingredientDropdown);

    ingredientInput.on('input', function() {
        var input = $(this).val();

        // Effectuer une requête AJAX pour récupérer les correspondances depuis le serveur
        $.ajax({
            url: 'ingredients/recherche', // Endpoint pour la recherche des ingrédients
            method: 'GET',
            data: { search: input },
            success: function(response) {
                ingredientDropdown.empty();

                // Ajouter les correspondances dans la liste déroulante
                response.ingredients.forEach(function(ingredient) {
                    var listItem = $('<li class="ingredient-item"></li>').text(ingredient.name);
                    listItem.on('click', function() {
                        ingredientInput.val(ingredient.name);
                        ingredientDropdown.empty();
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
});
