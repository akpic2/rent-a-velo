document.querySelectorAll('input[type="password"]').forEach((champ) => {
  const groupe = document.createElement('div');
  groupe.className = 'form__password';
  champ.before(groupe);
  groupe.append(champ);

  const bouton = document.createElement('button');
  bouton.type = 'button';
  bouton.className = 'button button--ghost';
  bouton.innerHTML = `
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
      <path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12Z"/>
      <circle cx="12" cy="12" r="3"/>
      <path class="password-eye__slash" d="M3 3l18 18" visibility="hidden"/>
    </svg>`;
  bouton.setAttribute('aria-label', 'Afficher le mot de passe');
  bouton.title = 'Afficher le mot de passe';
  bouton.setAttribute('aria-controls', champ.id);
  bouton.setAttribute('aria-pressed', 'false');
  groupe.append(bouton);

  bouton.addEventListener('click', () => {
    const visible = champ.type === 'password';
    champ.type = visible ? 'text' : 'password';
    const libelle = visible ? 'Masquer le mot de passe' : 'Afficher le mot de passe';
    bouton.setAttribute('aria-label', libelle);
    bouton.title = libelle;
    bouton.querySelector('.password-eye__slash').setAttribute('visibility', visible ? 'visible' : 'hidden');
    bouton.setAttribute('aria-pressed', String(visible));
  });
});
