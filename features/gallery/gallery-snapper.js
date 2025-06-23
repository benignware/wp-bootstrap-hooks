class Snapper {

  static ROW_SELECTOR = 'snapper-row';
  static COL_SELECTOR = 'snapper-col';

  constructor(element, config) {
    this._element = element;
    this._config = this._getConfig(config);
    this._currentIndex = 0;
    this._isSliding = false;
    this._itemCount = this._element.querySelectorAll(`.${Snapper.COL_SELECTOR}`).length;
    this._step = 0;
    this._interval = null;
    this._row = this._element.querySelector(`.${Snapper.ROW_SELECTOR}`);
    this._init();
  }

  _getConfig(config) {
    return {
      ...{
        interval: 5000,
        wrap: true,
        touch: true,
        pause: 'hover'
      },
      ...this._parseDataAttributes(),
      ...config
    };
  }

  _parseDataAttributes() {
    const config = {};
    if (this._element.hasAttribute('data-bs-interval')) {
      config.interval = this._element.getAttribute('data-bs-interval') === 'false' ? 
        false : Number(this._element.getAttribute('data-bs-interval'));
    }
    return config;
  }

  _init() {
    this._setupEventListeners();
    this._updateIndicators();
    if (this._config.interval) {
      this._setInterval();
    }
    this._calculateStep();
    window.addEventListener('resize', () => this._handleResize());
    this._handleResize();
  }

  _calculateStep() {
    // Calculate scroll step based on column width
    const firstCol = this._element.querySelector(`.${Snapper.COL_SELECTOR}`);

    console.log('Calculating step for:', firstCol);

    if (firstCol) {
      const bounds = firstCol.getBoundingClientRect();
      const colWidth = bounds.width;
      // const marginLeft = parseInt(window.getComputedStyle(firstCol).marginLeft);
      // const marginRight = parseInt(window.getComputedStyle(firstCol).marginRight);
      // const 
      
      console.log('Column width:', colWidth);
      this._step = colWidth;
    }
  }

  _handleResize() {
    console.log('Handling resize for:', this._element.id);
    // Recalculate step on resize
    this._calculateStep();

    // Adjust scroll position
    const columnsPerView = this._getColumnsPerView();
    const maxIndex = Math.max(0, this._itemCount - columnsPerView);
    
    if (this._currentIndex > maxIndex) {
      this._currentIndex = maxIndex;
      this._element.scrollTo({
        left: this._currentIndex * this._step,
        behavior: 'smooth'
      });
    }
    this._updateIndicators();
  }

  _setupEventListeners() {
    // Handle prev/next buttons
    document.querySelectorAll(`[data-bs-target="#${this._element.id}"][data-bs-slide]`).forEach(button => {
      button.addEventListener('click', e => {
        console.log('Button clicked:', button);
        e.preventDefault();
        const direction = button.getAttribute('data-bs-slide');
        this[direction === 'next' ? 'next' : 'prev']();
      });
    });

    // Handle indicators
    document.querySelectorAll(`[data-bs-target="#${this._element.id}"][data-bs-slide-to]`).forEach(button => {
      button.addEventListener('click', e => {
        console.log('Indicator clicked:', button);
        e.preventDefault();
        const slideTo = parseInt(button.getAttribute('data-bs-slide-to'));

        this.to(slideTo);
      });
    });

    // Handle scroll events
    this._element.addEventListener('scroll', () => {
      if (this._isSliding) return;
      
      const scrollLeft = this._element.scrollLeft;
      const newIndex = Math.round(scrollLeft / this._step);
      
      if (newIndex !== this._currentIndex) {
        this._currentIndex = newIndex;
        this._updateIndicators();
      }
    }, { passive: true });

    // Handle touch events
    if (this._config.touch) {
      let touchStartX = 0;
      let touchEndX = 0;

      this._element.addEventListener('touchstart', e => {
        touchStartX = e.changedTouches[0].screenX;
        if (this._config.pause === 'hover') {
          this.pause();
        }
      }, { passive: true });

      this._element.addEventListener('touchend', e => {
        touchEndX = e.changedTouches[0].screenX;
        this._handleSwipe();
        if (this._config.pause === 'hover') {
          this.cycle();
        }
      }, { passive: true });

      this._handleSwipe = () => {
        if (touchEndX < touchStartX - 50) {
          this.next();
        } else if (touchEndX > touchStartX + 50) {
          this.prev();
        }
      };
    }

    // Pause on hover
    if (this._config.pause === 'hover') {
      this._element.addEventListener('mouseenter', () => this.pause());
      this._element.addEventListener('mouseleave', () => this.cycle());
    }

    // Handle image loading
    const images = this._element.querySelectorAll('img');
    images.forEach(img => {
      img.addEventListener('load', () => {
        console.log('Image loaded:', img);
        this._update();
      });
      img.addEventListener('error', () => {
        console.error('Image failed to load:', img);
      });
    });
  }

  _update() {
    this._calculateStep();
    this._updateIndicators();
    this._updateControls();
  }

  _getColumnsPerView() {
    const bounds = this._element.getBoundingClientRect();
    
    if (bounds.width === 0) {
      return 0;
    }
    
    // Calculate how many columns fit in the current view
    if (this._step === undefined) {
      this._calculateStep();
    }

    if (this._step === 0) {
      return 0;
    }

    const width = bounds.width;

    console.log('Bounds:', bounds, this._step, width);

    const columnsPerView = Math.round(width / this._step);
    return columnsPerView;
  }

  _getSlideCount() {
    const columnsPerView = this._getColumnsPerView();

    return this._itemCount - columnsPerView + 1;
  }

  _updateIndicators() {
    const indicators = document.querySelectorAll(`[data-bs-target="#${this._element.id}"][data-bs-slide-to]`);
    const slideCount = this._getSlideCount();
    
    indicators.forEach((indicator, index) => {
      indicator.classList.toggle('active', index === this._currentIndex);
      indicator.setAttribute('aria-current', index === this._currentIndex ? 'true' : 'false');

      if (slideCount > 1 && index < slideCount) {
        indicator.style.display = 'inline-block';
      } else {
        indicator.style.display = 'none';
      }
    });

    this._updateControls();
  }

  _debugInfo() {
    return {
      currentIndex: this._currentIndex,
      itemCount: this._itemCount,
      step: this._step,
      isSliding: this._isSliding,
      elementId: this._element.id,
      config: this._config,
      columnsPerView: this._getColumnsPerView(),
      slideCount: this._getSlideCount()
    }
  }

  _updateControls() {
    const slideCount = this._getSlideCount();
    // const isNextVisible = slideCount > 1 && this._currentIndex < slideCount - 1;
    // const isPrevVisible = slideCount > 1 && this._currentIndex > 0;
    const isNextVisible = slideCount > 1;
    const isPrevVisible = slideCount > 1;

    console.log('UPDATING CONTROLS', this._debugInfo(), {
      isNextVisible,
      isPrevVisible,
      slideCount,
      currentIndex: this._currentIndex
    });

    const controls = document.querySelectorAll(`[data-bs-target="#${this._element.id}"][data-bs-slide]`);

    controls.forEach(control => {
      const isNext = control.getAttribute('data-bs-slide') === 'next';
      const isPrev = control.getAttribute('data-bs-slide') === 'prev';

      if (!isNext && !isPrev) {
        return;
      }

      if (isNext) {
        control.style.display = isNextVisible ? '' : 'none';
      }
      if (isPrev) {
        control.style.display = isPrevVisible ? '' : 'none';
      }
    });

    // const prevButton = this._element.querySelector('.carousel-control-prev');
    // const nextButton = this._element.querySelector('.carousel-control-next');
    // // const slideCount = this._getSlideCount();
    // // const columnsPerView = this._getColumnsPerView();

    // console.log(`Columns per view: ${columnsPerView}, Item count: ${this._itemCount}`);
    // console.log(`Slide count: ${slideCount}, Current index: ${this._currentIndex}`);

    // const controlsVisible = slideCount > 1;

    // console.log(`Controls visible: ${controlsVisible}`);
    // console.log(`Prev button: ${prevButton}, Next button: ${nextButton}`);
    
    // if (prevButton) {
    //   prevButton.style.display = controlsVisible ? '' : 'none';
    // }
    // if (nextButton) {
    //   nextButton.style.display = controlsVisible ? '' : 'none';
    // }
  }

  _setInterval() {
    this._clearInterval();
    this._interval = setInterval(() => this.next(), this._config.interval);
  }

  _clearInterval() {
    if (this._interval) {
      clearInterval(this._interval);
      this._interval = null;
    }
  }

  next() {
    if (this._isSliding) return;
    
    const slideCount = this._getSlideCount();
    const nextIndex = (this._currentIndex + 1) % slideCount;
    
    if (!this._config.wrap && nextIndex === 0) {
      this.pause();
      return;
    }

    console.log('Next index:', nextIndex);
    
    this.to(nextIndex);
  }

  prev() {
    if (this._isSliding) return;
    
    const slideCount = this._getSlideCount();
    const prevIndex = (this._currentIndex - 1 + slideCount) % slideCount;

    if (!this._config.wrap && prevIndex === slideCount - 1) {
      this.pause();
      return;
    }

    console.log('Previous index:', prevIndex);
    
    this.to(prevIndex);
  }

  to(index) {
    if (this._isSliding || index === this._currentIndex) return;
    
    this._isSliding = true;
    this._currentIndex = index;
    
    this._element.scrollTo({
      left: index * this._step,
      behavior: 'smooth'
    });
    
    this._updateIndicators();
    
    // Reset sliding state after transition
    setTimeout(() => {
      this._isSliding = false;
    }, 500);
  }

  pause() {
    if (this._interval) {
      this._clearInterval();
    }
  }

  cycle() {
    if (this._config.interval && !this._interval) {
      this._setInterval();
    }
  }

  static getOrCreateInstance(element, config = {}) {
    const instanceKey = `bs.snapper.${element.id}`;
    let instance = element[instanceKey];
    
    if (!instance) {
      instance = new Snapper(element, config);
      element[instanceKey] = instance;
    }
    
    if (typeof config === 'object') {
      instance._config = { ...instance._config, ...config };
    }
    
    return instance;
  }
}

// Initialize snappers when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.snapper-container').forEach(element => {
    Snapper.getOrCreateInstance(element);
  });
});

// Data API
document.querySelectorAll('[data-bs-toggle="snapper"]').forEach(element => {
  Snapper.getOrCreateInstance(element);
});

if (!window.bootstrapHooks) {
  window.bootstrapHooks = {};
}

// Add to window for global access
if (!window.bootstrapHooks.Snapper) {
  window.bootstrapHooks.Snapper = Snapper;
}