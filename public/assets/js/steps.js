var $stepsList = $('#steps-list');
var $addStepButton = $('#add-step');
var $addIngredientLink = $('#add-ingredient');

$(document).ready(function() {
    if (!$addIngredientLink.hasClass('edit-page'))
        $addStepButton.click();
});

// Ajouter une étape
$addStepButton.click(function(e) {
    e.preventDefault();
    var prototype = $stepsList.data('prototype');
    var index = $stepsList.children('li').length;
    var newForm = prototype.replace(/__name__/g, index);
    var $newItem = $('<li class="col-12 my-2">' + newForm + '</li>');
    var $deleteButton = $('<button class="delete-step btn btn-sm btn-danger my-3">Supprimer l\'étape</button>');
    $newItem.append($deleteButton);
    $stepsList.append($newItem);
});

// Supprimer une étape
$stepsList.on('click', '.delete-step', function(e) {
    e.preventDefault();
    $(this).closest('li').remove();
});
