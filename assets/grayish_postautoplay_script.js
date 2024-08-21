document.addEventListener('DOMContentLoaded', function () {
  const bodyElement = document.querySelector('body:not(.skin-grayish)');

  if (bodyElement) {
    const overflowX = window.getComputedStyle(bodyElement).overflowX;
    if (overflowX !== 'hidden' && overflowX !== 'clip') {
      bodyElement.style.overflowX = 'clip';
    }
  }
});