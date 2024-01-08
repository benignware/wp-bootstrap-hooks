
(() => {
  /**
   * Calculate brightness value by RGB or HEX color.
   * @param color (String) The color value in RGB or HEX (for example: #000000 || #000 || rgb(0,0,0) || rgba(0,0,0,0))
   * @returns (Number) The brightness value (dark) 0 ... 255 (light)
   */
  function getBrightness(color) {
    var color = "" + color, isHEX = color.indexOf("#") == 0, isRGB = color.indexOf("rgb") == 0;
    let r, g, b;

    if (isHEX) {
      var m = color.substr(1).match(color.length == 7 ? /(\S{2})/g : /(\S{1})/g);
      
      if (m) {
        r = parseInt(m[0], 16);
        g = parseInt(m[1], 16);
        b = parseInt(m[2], 16);
        a = 1;
      }
    }

    if (isRGB) {
      var m = color.match(/(\d+),\s*(\d+),\s*(\d+)(?:\s*,([\d.]+))?/);
      
      if (m) {
        r = m[1];
        g = m[2];
        b = m[3];
        a = m[4] || 1;
      }
    }

    if (r && g && b) {
      return ((r * 299)+(g * 587)+(b * 114)) / 1000;
    }

    return null;
  }

  const getCanvas = async() => {
    return new Promise((resolve, reject) => {
      const check = () => {
        const iframe = document.querySelector('iframe[name="editor-canvas"]');

        if (iframe) {
          resolve(iframe);
        } else {
          setTimeout(check, 500);
        }
      }

      check();
    });
  }

  let iframe;

  const getThemeData = () => {
    const iframeDocument = iframe.contentDocument || iframe.contentWindow.document;
    const themeData = {
      styles: {
        color: {
        }
      },
      settings: {}
    };

    [...iframeDocument.styleSheets].forEach(styleSheet => {
      [...styleSheet.cssRules].forEach(cssRule => {
        if (cssRule.selectorText === 'body') {
          if (cssRule.style.backgroundColor) {
            themeData.styles.color.background = cssRule.style.backgroundColor;
          }
        }
      });
      
    });
    
    return themeData;
  }

  const computedColor = (color) => {
    let computeElement = document.getElementById('bootstrapComputeElement');

    if (!computeElement) {
      computeElement = document.createElement('div');

      computeElement.style.display = 'none';
      computeElement.id = 'bootstrapComputeElement';
      document.body.appendChild(computeElement);
    }
    computeElement.style.backgroundColor = color;

    const style = window.getComputedStyle(computeElement);

    return style.backgroundColor;
  }

  const update = () => {
    const iframeDocument = iframe.contentDocument || iframe.contentWindow.document;
    const editorWrapper = iframeDocument.body;
    const themeData = getThemeData(); 
    const backgroundColor = themeData.styles.color.background;

    if (backgroundColor) {
      const computedBackgroundColor = computedColor(backgroundColor);

      if (computedBackgroundColor) {
        const brightness = getBrightness(computedBackgroundColor);

        if (brightness >= 0) {
          if (brightness < 100) {
            editorWrapper.setAttribute('data-bs-theme', 'dark');
          } else {
            editorWrapper.removeAttribute('data-bs-theme');
          }
        }
      }
    }
  };

  let initialized = false;

  const init = async () => {
    if (initialized) {
      return;
    }

    initialized = true;
    iframe = await getCanvas();

    const iframeDocument = iframe.contentDocument || iframe.contentWindow.document;

    iframeDocument.addEventListener('DOMContentLoaded', () => {
      update();
    });

    update();

    iframe.contentWindow.addEventListener('load', () => {
      update();
    });

    const editorWrapper = iframeDocument.body;

    const observer = new MutationObserver(styleChangedCallback);

    observer.observe(editorWrapper, {
        attributes: true,
        attributeFilter: ['style', 'class'],
    });
    
    function styleChangedCallback(mutations) {
      update();
    }
  };

  init();
})();
