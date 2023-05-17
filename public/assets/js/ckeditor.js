BalloonEditor
    .create(
        document.querySelector("#editor")
    )
    .then(editor => {
        editor.sourceElement.parentElement.addEventListener("submit", function(e){
            e.preventDefault();
            document.querySelector("#newsletters_form_content").value = editor.getData();
            this.submit();
        })
    });