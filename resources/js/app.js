//
// Aplica realce suave somente aos cards genericos renderizados na pagina.
document.querySelectorAll('.card').forEach(card => {

    card.addEventListener('mouseenter', () => {

        card.style.transform = 'scale(1.03)';

    });

    card.addEventListener('mouseleave', () => {

        card.style.transform = 'scale(1)';

    });

});