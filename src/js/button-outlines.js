const SVG_NAMESPACE = 'http://www.w3.org/2000/svg';
const SELECTOR = [
  '.landing-hero__content > .landing-hero__cta',
  '.landing-hero__header-cta',
  '.split-form__calculator',
  '.landing-slider-arrow:not(.landing-slider-arrow--next)',
  '.mobile-actions__calculator',
].join(',');

// Coordinates use the button's actual pixels: the cuts and stroke never stretch.
export const outlinePath = (width, height, stroke, cut, cutLeft = true) => {
  const inset = stroke / 2;
  const right = width - inset;
  const bottom = height - inset;
  const corner = Math.min(cut, width / 2, height / 2);
  return `M${cutLeft ? corner : inset} ${inset}H${right}V${height - corner}L${width - corner} ${bottom}H${inset}${cutLeft ? `V${corner}` : ''}Z`;
};

export const initButtonOutlines = () => {
  const paths = new Map();
  const update = (button, width, height) => {
    if (width <= 0 || height <= 0) return;
    const stroke = button.matches('.landing-slider-arrow') ? 1 : 2;
    paths.get(button).setAttribute('d', outlinePath(width, height, stroke, 12, !button.matches('.mobile-actions__calculator')));
  };
  const observer = new ResizeObserver((entries) => {
    entries.forEach(({ target, borderBoxSize, contentRect }) => {
      const box = borderBoxSize?.[0];
      update(target, box?.inlineSize ?? target.offsetWidth ?? contentRect.width, box?.blockSize ?? target.offsetHeight ?? contentRect.height);
    });
  });

  document.querySelectorAll(SELECTOR).forEach((button) => {
    if (button.querySelector('.button-outline__frame')) return;
    button.classList.add('button-outline');
    const frame = document.createElementNS(SVG_NAMESPACE, 'svg');
    frame.classList.add('button-outline__frame');
    frame.setAttribute('aria-hidden', 'true');
    frame.setAttribute('focusable', 'false');
    const path = document.createElementNS(SVG_NAMESPACE, 'path');
    path.setAttribute('stroke-width', button.matches('.landing-slider-arrow') ? '1' : '2');
    path.setAttribute('vector-effect', 'non-scaling-stroke');
    frame.append(path);
    button.prepend(frame);
    paths.set(button, path);
    update(button, button.offsetWidth, button.offsetHeight);
    observer.observe(button);
  });
};
