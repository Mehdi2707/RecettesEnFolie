$(document).ready(function() {
    // Fonction pour attacher les écouteurs d'événements aux boutons "Répondre"
    function attachReplyEventListeners() {
        document.querySelectorAll("[data-reply]").forEach(function(element) {
            element.addEventListener("click", function(e) {
                e.preventDefault();
                let formElement = $("#ajout-commentaire");
                $("html, body").animate(
                    {
                        scrollTop: formElement.offset().top,
                    },
                    800
                );

                document.querySelector("#elementsForReply").innerHTML =
                    "<p>Répondre à @" +
                    this.dataset.user +
                    '&nbsp;&nbsp;<button id="stopReply" class="btn btn-sm btn-danger">Ne plus répondre</button></p>';
                document.querySelector("#comments_form_parentid").value = this.dataset.id;
            });
        });
    }

    // Attachez les écouteurs d'événements aux boutons "Répondre" existants
    attachReplyEventListeners();

    $(document).on("click", "#stopReply", function() {
        document.querySelector("#comments_form_parentid").value = 0;
        document.querySelector("#elementsForReply").innerHTML = "";
    });

    $(document).on("click", ".edit-comment-btn", function(e) {
        e.preventDefault();
        const commentId = $(this).data("id");
        const commentContent = $(this).siblings("span").text();
        const urlRequest = $(this).data("request");

        // Remplacez le contenu du commentaire par un formulaire d'édition
        var formHtml =
            '<form class="edit-comment-form" action="' +
            urlRequest +
            '" method="post">' +
            '<input type="hidden" name="comment_id" value="' +
            commentId +
            '">' +
            '<textarea class="form-control" name="comment_content">' +
            commentContent +
            "</textarea>" +
            '<button class="btn btn-sm" type="submit">Enregistrer</button>' +
            "</form>";
        $(this).siblings("span").replaceWith(formHtml);
    });

    // Gérez la soumission du formulaire d'édition avec AJAX
    $(document).on("submit", ".edit-comment-form", function(e) {
        e.preventDefault();
        var form = $(this);
        var commentId = form.find('input[name="comment_id"]').val();
        var commentContent = form.find('textarea[name="comment_content"]').val();

        // Envoyez la requête AJAX pour mettre à jour le commentaire
        $.ajax({
            url: form.attr("action"),
            type: "POST",
            data: {
                comment_id: commentId,
                comment_content: commentContent,
            },
            success: function(response) {
                // Mettez à jour le contenu du commentaire avec la nouvelle valeur
                form.replaceWith('<span>' + commentContent + "</span>");
            },
            error: function(xhr, status, error) {
                alert(xhr.responseJSON.message);
            },
        });
    });

    let url = $("#load-comments-btn").data("url");
    // Page à charger
    let offset = 2;

    // Fonction pour charger les commentaires supplémentaires
    function loadMoreComments() {
        $.ajax({
            url: url,
            type: "POST",
            dataType: "json",
            data: { offset: offset },
            success: function(response) {
                // Insérez les commentaires chargés dans le DOM

                var commentsData = response.data;

                commentsData.forEach(function(comment) {
                    const createdAt = comment.createdAt;
                    const updatedAt = comment.updatedAt;

                    const createdAtObj = new Date(createdAt.date);
                    const updatedAtObj = new Date(updatedAt.date);

                    const formattedCDate = createdAtObj.toLocaleDateString();
                    const formattedCTime = createdAtObj.toLocaleTimeString([], {
                        hour: "numeric",
                        minute: "numeric",
                    });

                    const formattedUDate = updatedAtObj.toLocaleDateString();
                    const formattedUTime = updatedAtObj.toLocaleTimeString([], {
                        hour: "numeric",
                        minute: "numeric",
                    });

                    var commentHtml =
                        '<div class="my-3">' +
                        '<span>' +
                        'Publié par&nbsp;' +
                        '<a href="' +
                        "/profil/" +
                        comment.user.username +
                        '" class="badge badge-light" style="font-size: 15px;">@' +
                        comment.user.username +
                        "</a>" +
                        "&nbsp;le " +
                        formattedCDate +
                        " à " +
                        formattedCTime;

                    if (createdAtObj.getTime() !== updatedAtObj.getTime()) {
                        commentHtml +=
                            '&nbsp;(modifié le ' +
                            formattedUDate +
                            ' à ' +
                            formattedUTime +
                            ")";
                    }

                    commentHtml +=
                        "</span>" +
                        '<div class="card">' +
                        '<div class="card-body">' +
                        '<span class="comment-content">' +
                        comment.content +
                        "</span>";

                    if (comment.user.username === $("#load-comments-btn").data("user")) {
                        commentHtml +=
                            '<a class="float-right edit-comment-btn" href="#" data-request="/modification-commentaire" data-id="' +
                            comment.id +
                            '">Modifier</a>';
                    }

                    commentHtml += "</div>";

                    commentHtml += generateReplyHTML(comment.replies, false);

                    commentHtml += "</div>";

                    if(comment.replies)
                    {
                        var lengthReply = 1;

                        while(lengthReply <= comment.replies.length)
                        {
                            commentHtml += '</div>';
                            lengthReply++;
                        }
                    }

                    if($("#load-comments-btn").data("user") !== '')
                        commentHtml += '<p><a class="btn btn-sm btn-info" href="#ajout-commentaire" data-user="' +
                            comment.user.username +
                            '" data-reply data-id="' +
                            comment.id +
                            '">Répondre</a></p>';

                    commentHtml += "</div></div>";

                    $("#load-comments-btn").before(commentHtml);
                });

                function generateReplyHTML(replies, isNestedReply) {
                    var replyHtml = "";
                    var isFirstIteration = true;

                    if (replies && replies.length > 0) {
                        replyHtml += '<div class="replies">';

                        replies.forEach(function(reply) {
                            const createdAtR = reply.createdAt;
                            const updatedAtR = reply.updatedAt;

                            const createdAtObjR = new Date(createdAtR.date);
                            const updatedAtObjR = new Date(updatedAtR.date);

                            const formattedCDateR = createdAtObjR.toLocaleDateString();
                            const formattedCTimeR = createdAtObjR.toLocaleTimeString([], {
                                hour: "numeric",
                                minute: "numeric",
                            });

                            const formattedUDateR = updatedAtObjR.toLocaleDateString();
                            const formattedUTimeR = updatedAtObjR.toLocaleTimeString([], {
                                hour: "numeric",
                                minute: "numeric",
                            });

                            replyHtml +=
                                '<div class="' + (isFirstIteration === true ? 'm-2' : '') + '">' +
                                '<span>' +
                                'Réponse publiée par&nbsp;' +
                                '<a href="' +
                                "/profil/" +
                                reply.user.username +
                                '" class="badge badge-light" style="font-size: 15px;">@' +
                                reply.user.username +
                                "</a>" +
                                "&nbsp;le " +
                                formattedCDateR +
                                " à " +
                                formattedCTimeR;

                            if(isFirstIteration)
                                isFirstIteration = false;

                            if (createdAtObjR.getTime() !== updatedAtObjR.getTime()) {
                                replyHtml +=
                                    '&nbsp;(modifié le ' +
                                    formattedUDateR +
                                    ' à ' +
                                    formattedUTimeR +
                                    ")";
                            }

                            replyHtml +=
                                "</span>" +
                                '<div class="card">' +
                                '<div class="card-body">' +
                                '<span class="comment-content">' +
                                reply.content +
                                "</span>";

                            if (reply.user.username === $("#load-comments-btn").data("user")) {
                                replyHtml +=
                                    '<a class="float-right edit-comment-btn" href="#" data-request="/modification-commentaire" data-id="' +
                                    reply.id +
                                    '">Modifier</a>';
                            }

                            replyHtml += "</div>";

                            replyHtml += generateReplyHTML(reply.replies, true);

                            replyHtml += "</div>";

                            if(reply.replies)
                            {
                                var lengthReply = 1;

                                while(lengthReply <= reply.replies.length)
                                {
                                    replyHtml += '</div>';
                                    lengthReply++;
                                }
                            }

                            if (!isNestedReply) {
                                // Ajouter le bouton "Répondre" uniquement pour les réponses aux commentaires principaux
                                if($("#load-comments-btn").data("user") !== '')
                                    replyHtml +=
                                        '<p><a class="btn btn-sm btn-info" href="#ajout-commentaire" data-user="' +
                                        reply.user.username +
                                        '" data-reply data-id="' +
                                        reply.id +
                                        '">Répondre</a></p>';
                            }
                        });

                        replyHtml += "</div>";
                    }

                    return replyHtml;
                }

                offset += 1;

                if (commentsData.length < 5) {
                    $("#load-comments-btn").remove();
                }

                // Attachez les écouteurs d'événements aux nouveaux boutons "Répondre"
                attachReplyEventListeners();
            },
            error: function(xhr, status, error) {
                console.error(error);
            },
        });
    }

    $("#load-comments-btn").on("click", function() {
        loadMoreComments();
    });
});
