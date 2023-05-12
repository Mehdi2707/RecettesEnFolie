$(document).ready(function()
{
    const stars = document.querySelectorAll(".stars-form");
    const note = document.querySelector("#note");
    const urlRequest = note.dataset.request;

    for(star of stars)
    {
        star.addEventListener("mouseover", function()
        {
            resetStars();
            this.classList.add("fa-star");
            this.classList.remove("fa-star-o");

            let previousStar = this.previousElementSibling;
            while(previousStar)
            {
                previousStar.classList.add("fa-star");
                previousStar.classList.remove("fa-star-o");
                previousStar = previousStar.previousElementSibling;
            }
        });

        star.addEventListener("click", function()
        {
            note.value = this.dataset.value;
            $.ajax({
                url: urlRequest,
                method: 'POST',
                data: { note: note.value },
                success: function(response) {
                    alert(response);
                },
                error: function(response) {
                    alert(response);
                }
            });
        });

        star.addEventListener("mouseout", function()
        {
            resetStars(note.value);
        });
    }

    function resetStars(note = 0)
    {
        for(star of stars)
        {
            if(star.dataset.value > note)
            {
                star.classList.add("fa-star-o");
                star.classList.remove("fa-star");
            }
            else
            {
                star.classList.add("fa-star");
                star.classList.remove("fa-star-o");
            }
        }
    }
});