$(document).ready(function ()
{
    document.querySelectorAll("[data-reply]").forEach(element =>
    {
        element.addEventListener("click", function(){
            document.querySelector("#comments_form_parentid").value = this.dataset.id;
        });
    });
});