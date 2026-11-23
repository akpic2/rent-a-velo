// Recherche dynamique (AJAX) : à chaque lettre tapée, on demande au serveur
// les lignes du tableau correspondantes, sans recharger la page.
const champRecherche = document.querySelector('#recherche');
const lignes = document.querySelector('#reservations-lignes');

champRecherche.addEventListener('input', async () => {
  // encodeURIComponent protège les espaces, accents et caractères spéciaux dans l'URL
  const url = `index.php?url=admin-recherche&q=${encodeURIComponent(champRecherche.value)}`;

  const reponse = await fetch(url);
  lignes.innerHTML = await reponse.text();
});
