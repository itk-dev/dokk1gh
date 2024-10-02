// Styling
import './scss/webapp.scss'

// Fonts
import '@fontsource/montserrat'

// Fontawsome icons
import { library, dom } from '@fortawesome/fontawesome-svg-core'
import {
  faLockOpen,
  faIdCard,
  faInfoCircle,
  faBan,
  faWifi,
  faCheckSquare,
  faCircleNotch
} from '@fortawesome/free-solid-svg-icons'

// JavaScrips
import 'add-to-homescreen'
import 'clipboard'
import './js/ios_stay_standalone'
library.add(
  faLockOpen,
  faIdCard,
  faInfoCircle,
  faBan,
  faWifi,
  faCheckSquare,
  faCircleNotch
)
dom.watch()
