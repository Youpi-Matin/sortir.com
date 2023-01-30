import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  labels = {
    inscrire: '',
    desister: 'X'
  }

  inverserAction = (string) => {
    return string === "inscrire" ? "desister" : "inscrire";
  }

  toggleLiens = () => {
    this.element.querySelectorAll("#sortie-actions-participant a").forEach((lien) => {
      lien.classList.toggle("hidden");
    });
  }

  majCompteur = (count) => {
    this.element.querySelector("#sortie-action-count").innerHTML = count;
  }

  majInscrit = (action) => {
    console.log(action);
    this.element.querySelector("#sortie-action-statut").innerHTML = this.labels[action];
  }

  modifierInscription = (url, action) => {
    fetch(url, {
      method: 'GET'
    })
      .then((response) => {
        response.json().then((data) => {
          alert(data.message);
          if (data.status === "ok") {
            this.element.dataset.sortieAction = this.inverserAction(action);
            this.majInscrit(this.inverserAction(action));
            this.majCompteur(data.count);
            this.toggleLiens();
          }
        })
      })
      .catch((error) => {
        console.log(error.message);
      })
  }

  connect() {
    this.element.querySelectorAll("#sortie-actions-participant a").forEach((element) => {
      element.addEventListener('click', (event) => {
        event.preventDefault();
        let action = this.element.dataset.sortieAction;
        this.modifierInscription(event.target.href, action);
      })
    });
  }
}