window.onload = () => {
    // gestion des boutons "supprimer"
    let links = document.querySelectorAll("[data-delete]");

    // on boucle sur links

    for(link of links){
        // on écoute le click
        link.addEventListener("click", function(e){
            // on empêche la navigation
            e.preventDefault()

            if(confirm("Voulez-vous supprimer cette image?")){
                // on envois une requete ajax vers le href du lien avec la méthode DELETE
                fetch(this.getAttribute('href'),{
                    method: "DELETE",
                    headers: {
                        "X-Requested-with": "XMLHttpRequest",
                        "Content-type": "application/json"
                    },
                    body: JSON.stringify({'_token': this.dataset.token})
                }).then(
                    // on récupère la réponse en JSON
                    response => response.json()
                ).then(data => {
                    if(data.success){
                        this.parentElement.remove()
                    }else{
                        alert('data.error')
                    }
                }).catch(e=> alert(e))
            }
        })
    }
}