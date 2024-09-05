document.addEventListener('DOMContentLoaded', function () {
  const bodyElement = document.querySelector('body:not(.skin-grayish)');
  const ContainerElement = document.querySelector('body:not(.skin-grayish) .container');

  if (bodyElement) {
    const overflowX = window.getComputedStyle(bodyElement).overflowX;
    if (overflowX !== 'hidden' && overflowX !== 'clip') {
      bodyElement.style.overflowX = 'clip';
    }
  }
  if (ContainerElement) {
    const overflowX = window.getComputedStyle(ContainerElement).overflowX;
    if (overflowX !== 'clip') {
      ContainerElement.style.overflowX = 'clip';
    }
  }

});