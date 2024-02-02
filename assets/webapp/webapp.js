// Styling
import './scss/webapp.scss';

// Fonts
import '@fontsource/montserrat';

// start the Stimulus application
//import '../bootstrap';

// jQuery
const $ = require('jquery');

console.log("TEST");
// Fontawsome icons
import '@fortawesome/fontawesome-svg-core';
import {
    faLockOpen,
    faIdCard,
    faInfoCircle,
    faBan,
    faWifi,
    faCheckSquare,
    faCircleNotch
} from '@fortawesome/free-solid-svg-icons';
library.add(
    faLockOpen,
    faIdCard,
    faInfoCircle,
    faBan,
    faWifi,
    faCheckSquare,
    faCircleNotch
);

// JavaScrips
import 'add-to-homescreen';
import 'clipboard';
import './js/ios_stay_standalone';

