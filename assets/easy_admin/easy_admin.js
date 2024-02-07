import './easy_admin.scss';

import bootstrap from "bootstrap/dist/js/bootstrap.bundle";

// @see https://getbootstrap.com/docs/5.3/components/tooltips/#enable-tooltips
const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))

addEventListener('load', () => {
    document.querySelectorAll('[data-clipboard-text]').forEach((el) => {
        if (navigator.clipboard) {
            el.addEventListener('click', () => {
                const text = el.dataset.clipboardText
                navigator.clipboard.writeText(text)
                    .then(() => {
                        if (!el.tooltip) {
                            el.tooltip = new bootstrap.Tooltip(el, {
                                title: el.dataset.clipboardSuccess ?? `"${text}" was copied to your clipboard.`
                            })
                            el.tooltip.show()
                        }
                    })
                    .catch(err => {
                        console.error(el.dataset.clipboardError ?? `Error copying text to clipboard: ${err}`)
                    })
            })
        } else {
            el.hidden = true
        }
    })
})

