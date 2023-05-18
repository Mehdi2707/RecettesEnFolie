$(document).ready(function()
{
    const favoriteButton = document.querySelector('#favorite');

    favoriteButton.addEventListener("click", function()
    {
        let urlRequest = favoriteButton.dataset.request;

        $.ajax({
            url: urlRequest,
            method: 'POST',
            data: { },
            success: function(response) {

                let route1 = '';
                let route2 = '';
                let status = '';
                let className = '';
                let text = '';

                if(favoriteButton.dataset.status === 'true')
                {
                    route1 = '/suppression/';
                    route2 = '/ajout/';
                    status = false;
                    className = 'text-success';
                    text = '\u00A0\u00A0Ajouter aux favoris';
                }
                else
                {
                    route1 = '/ajout/';
                    route2 = '/suppression/';
                    status = true;
                    className = 'text-warning';
                    text = '\u00A0\u00A0Enlever des favoris';
                }

                favoriteButton.dataset.request = urlRequest.replace(route1, route2);
                favoriteButton.dataset.status = status;
                urlRequest = favoriteButton.dataset.request;

                // Création d'un nouvel élément <i>
                const newIcon = document.createElement('i');
                newIcon.classList.add('fa', 'fa-bookmark', className);
                newIcon.setAttribute('aria-hidden', 'true');

                // Création d'un nouvel élément de texte
                const newText = document.createTextNode(text);

                // Suppression du contenu précédent
                while (favoriteButton.firstChild) {
                    favoriteButton.removeChild(favoriteButton.firstChild);
                }

                // Ajout des nouveaux éléments au bouton
                favoriteButton.appendChild(newIcon);
                favoriteButton.appendChild(newText);
            },
            error: function(response) {

            }
        });
    });
});