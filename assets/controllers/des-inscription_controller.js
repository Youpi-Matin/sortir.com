import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  changeLink = (action) => {
    let link = this.element.querySelector("a");

    if (action == "inscrire") {
      this.element.dataset.sortieAction = "desinscrire";
      link.innerHTML = "desinscrire"
    } else {
      this.element.dataset.sortieAction = "inscrire";
      link.innerHTML = 'inscrire'
    }
  };

  toPerform = (url, action) => {
    fetch(url + '?action=' + action, {
      method: 'GET',
      mode: 'cors',
    })
      .then((response) => {
        if (response.ok) {
          alert("L'action a bien été effectuée");
          this.changeLink(action);
        } else {
          console.log('Erreur avec la requête');
        }
      })
      .catch((error) => {
        console.log(error.message);
      });
  };

  connect() {
    this.element.querySelector("a").addEventListener('click', (event) => {
      event.preventDefault();

      let action = this.element.dataset.sortieAction;
      let url = event.target.href;

      this.toPerform(url, action);
    })
  }
}