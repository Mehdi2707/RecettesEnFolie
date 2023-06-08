$(document).ready(function ()
{
    document.querySelectorAll("[data-reply]").forEach(element =>
    {
        element.addEventListener("click", function(e){
            e.preventDefault();
            let formElement = $("#ajout-commentaire");
            $('html, body').animate({
                scrollTop: formElement.offset().top
            }, 800);

            document.querySelector('#elementsForReply').innerHTML = "<p>Répondre à @" + this.dataset.user + "&nbsp;&nbsp;<button id=\"stopReply\" class=\"btn btn-sm btn-danger\">Ne plus répondre</button></p>";
            document.querySelector("#comments_form_parentid").value = this.dataset.id;
        });
    });

    $(document).on('click', '#stopReply', function() {
        document.querySelector("#comments_form_parentid").value = 0;
        document.querySelector('#elementsForReply').innerHTML = "";
    });
});

$(document).on('click', '.edit-comment-btn', function(e) {
    e.preventDefault();
    const commentId = $(this).data('id');
    const commentContent = $(this).siblings('span').text();
    const urlRequest = $(this).data('request');

    // Remplacez le contenu du commentaire par un formulaire d'édition
    var formHtml = '<form class="edit-comment-form" action="' + urlRequest + '" method="post">' +
    '<input type="hidden" name="comment_id" value="' + commentId + '">' +
    '<textarea class="form-control" name="comment_content">' + commentContent + '</textarea>' +
    '<button class="btn btn-sm" type="submit">Enregistrer</button>' +
    '</form>';
    $(this).siblings('span').replaceWith(formHtml);
});

// Gérez la soumission du formulaire d'édition avec AJAX
$(document).on('submit', '.edit-comment-form', function(e) {
    e.preventDefault();
    var form = $(this);
    var commentId = form.find('input[name="comment_id"]').val();
    var commentContent = form.find('textarea[name="comment_content"]').val();

    // Envoyez la requête AJAX pour mettre à jour le commentaire
    $.ajax({
        url: form.attr('action'),
        type: 'POST',
        data: {
            comment_id: commentId,
            comment_content: commentContent
        },
        success: function(response) {
            // Mettez à jour le contenu du commentaire avec la nouvelle valeur
            form.replaceWith('<span>' + commentContent + '</span>');
        },
        error: function(xhr, status, error) {
            alert(xhr.responseJSON.message);
        }
    });
});
