/* Bring a named verse into view. The address /bible/john/3/16 names a line, so
   the reader should land on it rather than at the top of the chapter and have
   to hunt. Only the page that was given a verse loads this. */

const verse = document.querySelector('[data-verse]');

if (verse) {
    verse.scrollIntoView({block: 'center'});
}
