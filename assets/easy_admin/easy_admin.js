import './easy_admin.scss';

import bootstrap from "bootstrap/dist/js/bootstrap.bundle";

// @see https://getbootstrap.com/docs/5.3/components/tooltips/#enable-tooltips
const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))

