console.log('EDITOR');



(() => {
  console.log('DO IT', wp.data.select('core/editor'));
  window.addEventListener('load', () => {
    console.log('DOM CONTENT READY');

    const body = document.querySelector('iframe');

    console.log('body: ', body);

    // body.setAttribute('data-bs-theme', 'dark');
  });
})();
