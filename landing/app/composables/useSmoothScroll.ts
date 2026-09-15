/**
 * Défilement animé vers une section de la page.
 *
 * Remplace le gestionnaire jQuery d'origine
 * ($('html, body').animate({ scrollTop: target.offset().top }, 600)) :
 * même cible (le haut de la section), et le hash n'est pas ajouté à l'URL.
 */
export function useSmoothScroll() {
  function scrollToSection(id: string) {
    const target = document.getElementById(id)
    if (!target) return

    window.scrollTo({
      top: target.getBoundingClientRect().top + window.scrollY,
      behavior: 'smooth',
    })
  }

  return { scrollToSection }
}
