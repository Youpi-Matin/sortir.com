import { Controller } from '@hotwired/stimulus';

export default class extends Controller {

    updateListeLieux(data) {
        let selectLieux = document.querySelector("select#sortie_creation_lieu");
        let options = selectLieux.innerHTML;
        options += `<option value="${data.id}" selected>${data.nom}</option>`;
        selectLieux.innerHTML = options;
    }

    submitModal(body) {
        fetch('/api/lieu/ajout/', {
            method: 'POST',
            body: body,
        }).then((response) => {
            response.json().then((data) => {
                this.updateListeLieux(data);
            })
        })
    }

    openModal(selecteur) {
        let modal = document.querySelector(selecteur);
        let form = modal.querySelector('form');

        form.addEventListener('submit', (event) => {
            event.preventDefault();

            let body = new FormData(form);

            this.submitModal(body);
        })

        modal.classList.remove('hidden');
    }

    loadModal(selecteur, villeId) {
        fetch('/api/lieu/ajout/?ville='.concat(villeId), {
            method: 'GET'
        }).then((response) => {
            response.json().then((data) => {
                let modalLieu = document.querySelector(selecteur);
                modalLieu.innerHTML = data;

                this.openModal(selecteur);
            })
        })
    }

    emptyModal(selecteur) {
        let modal = document.querySelector(selecteur);
        modal.classList.add('hidden');
        modal.innerHTML = '';
    }

    connect() {
        let modalId = '#modalLieu';

        let selectVille = document.querySelector('select#sortie_creation_ville');

        selectVille.addEventListener('change', () => {
            this.emptyModal(modalId)
        });

        this.element.addEventListener('click', () => {
            this.loadModal(modalId, selectVille.value);
        })
    }
}