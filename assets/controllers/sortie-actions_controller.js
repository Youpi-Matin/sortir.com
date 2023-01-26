import { Controller } from '@hotwired/stimulus';

export default class extends Controller {

  lienLabels = {
    inscrire: "S'inscrire",
    desinscrire: "Se désinscrire"
  };

  inverserAction = (action) => {
    return action === 'inscrire' ? 'desinscrire' : 'inscrire';
  }

  majLabels = (action) => {
    let statutContainer = this.element.querySelector("#sortie-action-statut");
    let lienContainer = this.element.querySelector("#sortie-action-lien > a:first-of-type");

    if (action === "desinscrire") {
      statutContainer.innerHTML = "X";
      lienContainer.innerHTML = this.lienLabels[action];
    } else {
      statutContainer.innerHTML = "";
      lienContainer.innerHTML = this.lienLabels[action];
    }
  }

  majInscription = (url, action) => {
    fetch(url + "?action=" + action)
      .then((response) => {
        if (response.ok) {
          response.json()
            .then((data) => {
              alert(data.message);
              this.element.dataset.sortieAction = this.inverserAction(action);
              this.majLabels(this.inverserAction(action));
              this.element.querySelector("#sortie-action-count").innerHTML = data.count;
            })
        }
      })
      .catch((error) => {
        console.log(error.message);
      });
  };

  connect() {
    let lienInscription = this.element.querySelector("#sortie-action-lien > a:first-of-type");

    lienInscription.addEventListener('click', (event) => {
      event.preventDefault();
      this.majInscription(lienInscription.href, this.element.dataset.sortieAction);
    })

    this.majLabels(this.element.dataset.sortieAction);
  }
}