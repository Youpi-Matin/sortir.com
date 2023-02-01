import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  labels = {
    inscrire: '',
    desister: 'X'
  }

  labelsLiens = {
    inscrire: 'S\'inscrire',
    desister: 'Se désister',
  }

  inverserAction = (string) => {
    return string === "inscrire" ? "desister" : "inscrire";
  }

  toggleLiens = (action) => {
    this.element.querySelectorAll("#sortie-actions-participant a").forEach((lien) => {
      // lien.classList.toggle("hidden");
      lien.dataset.sortieAction = action;
      lien.innerHTML = this.labelsLiens[action];
      lien.href = lien.href.replace(this.inverserAction(action), action);
    });
  }

  majCompteur = (count) => {
    this.element.querySelector("#sortie-action-count").innerHTML = count;
  }

  majInscrit = (action) => {
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
            console.log(data.action);
            this.toggleLiens(data.action);
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