/* Copy a value to the clipboard.

   A page opts in by marking the button that copies with data-copy="<value>"
   rather than by writing any script of its own. One listener handles every
   button there is, now and later. */

document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-copy]');

    if (!button) {
        return;
    }

    const label = button.innerHTML;

    navigator.clipboard.writeText(button.dataset.copy).then(() => {
        button.innerHTML = '<i class="fa-solid fa-check"></i> Copied';

        setTimeout(() => {
            button.innerHTML = label;
        }, 1500);
    });
});
