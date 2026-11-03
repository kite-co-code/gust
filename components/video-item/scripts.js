/**
 * Video item: the play button opens the video in a modal <dialog> and loads
 * the iframe (its src is held in data-src until then). Closing the dialog, with
 * the close button, Escape or a click outside the video, unloads the iframe and
 * returns focus to the play button. Markup: template.php.
 */

import dynamicElements from '../../assets/scripts/helpers/dynamicElements.js';

class VideoItem {
    constructor(element) {
        this.el = element;
        this.play = element.querySelector('.js-video-item-play');
        this.dialog = element.querySelector('.video-item__video');
        this.close = element.querySelector('.video-item__video-close');
        this.wrap = element.querySelector('.video-item__video-wrap');
        this.iframe = element.querySelector('iframe[data-src]');

        // Bail early - required markup not found.
        if (!this.play || !this.dialog || !this.iframe || typeof this.dialog.showModal !== 'function') {
            return;
        }

        this.play.addEventListener('click', this.open);
        this.close?.addEventListener('click', this.closeDialog);
        this.dialog.addEventListener('click', this.onDialogClick);
        this.dialog.addEventListener('close', this.onClose);
    }

    open = () => {
        this.iframe.src = this.iframe.dataset.src;
        this.el.classList.add('video-item--play');
        document.documentElement.classList.add('no-scroll');
        this.dialog.showModal();
    };

    closeDialog = () => {
        this.dialog.close();
    };

    /** Clicks outside the video (on the dialog's backdrop area) close it. */
    onDialogClick = (event) => {
        if (!this.wrap?.contains(event.target)) {
            this.closeDialog();
        }
    };

    /** Runs however the dialog closes, including Escape. */
    onClose = () => {
        this.el.classList.remove('video-item--play');
        // Unload the video so it stops playing.
        this.iframe.src = 'about:blank';
        document.documentElement.classList.remove('no-scroll');
        this.play.focus();
    };
}

dynamicElements.define('[data-video-item]', (element) => new VideoItem(element));
