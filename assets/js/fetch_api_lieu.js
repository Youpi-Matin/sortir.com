window.onload = () => {
    villeSelector = document.querySelector("#sortie_creation_ville");
    if (villeSelector) {
        villeSelector.addEventListener("change", initListeLieux);
    }
}

function initListeLieux(e) {
    villeId = (e.target.options.selectedIndex);
    fetch('http://localhost/api/lieu/ville/'.concat(villeId),
        {
            method: "GET",
            headers: {'Accept': 'application/json'}
        }).then(response => response.json())
        .then(data => {
            let options = "";

            for (const lieu of data.lieux) {
                options += `<option value="${lieu.id}">${lieu.nom}</option>`;

            }
            document.querySelector('#sortie_creation_lieu').innerHTML = options;
        })
}
