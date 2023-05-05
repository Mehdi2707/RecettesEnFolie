// JavaScript pour gérer l'ajout dynamique d'ingrédients
var $addIngredientLink = $('#add-ingredient');
var ingredientList = $('#ingredients-list');
var urlSearch = $('#add-ingredient').data('url');

$(document).ready(function() {
    if (!$addIngredientLink.hasClass('edit-page'))
        $addIngredientLink.click();
});

$addIngredientLink.on('click', function(e) {
    e.preventDefault();

    var ingredientIndex = ingredientList.children().length;
    var ingredientPrototype = ingredientList.data('prototype');
    var modifiedPrototype = "<span class=\"fa fa-plus\" style=\"color: #15c215;font-size: 30px;\"></span>" + ingredientPrototype;
    modifiedPrototype = modifiedPrototype.replace('id="recipes_form_ingredients___name__"', 'id="recipes_form_ingredients___name__" class ="form-row"');
    modifiedPrototype = modifiedPrototype.replace(/<div>/g, '<div class="col-md-4">');

    var ingredientForm = modifiedPrototype.replace(/__name__/g, ingredientIndex);
    var newIngredientItem = $('<li></li>').html(ingredientForm);
    var removeIngredientLink = $('<a href="#" class="my-3 btn btn-sm btn-danger remove-ingredient">Supprimer l\'ingrédient</a>');
    newIngredientItem.append(removeIngredientLink);
    ingredientList.append(newIngredientItem);

    // Réattacher l'écouteur d'événement au champ d'entrée d'ingrédient initial
    var ingredientInput = newIngredientItem.find('.ingredient-name-input');
    var ingredientDropdown = $('<ul class="ingredient-dropdown list-group"></ul>');
    ingredientInput.after(ingredientDropdown);

    ingredientInput.on('input', function() {
        var input = $(this).val();

        if(input == '')
        {
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

    $(document).on('click', function(event) {
        var target = $(event.target);

        // Vérifier si l'événement de clic provient de l'élément de saisie d'ingrédient ou de la liste déroulante
        if (!target.is(ingredientInput) && !target.closest(ingredientDropdown).length) {
            // Cacher la liste déroulante
            ingredientDropdown.hide();
        }
    });

});

// Gérer la suppression d'un ingrédient
$(document).on('click', '.remove-ingredient', function(e) {
    e.preventDefault();
    $(this).closest('li').remove();
});
