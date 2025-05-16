import 'normalize.css';
import '@glidejs/glide/dist/css/glide.core.min.css';
import '../css/style.scss';

// Our modules / classes
import MobileMenu from './modules/MobileMenu';
import HeroSlider from './modules/HeroSlider';
import OpenStreetMap from './modules/OpenStreetMap';
import Search from './modules/Search';
import Codex from './modules/Codex';
import { singletonToast } from './modules/Toast';
import Likes from './modules/Likes';
import Lightbox from './modules/Lightbox';
import Contract from './modules/Contract';
import FormModal from './modules/FormModal';
import Table from './modules/Table';
import Calendar from './modules/Calendar';

// Instantiate a new object using our modules/classes
const mobileMenu = new MobileMenu();
const heroSlider = new HeroSlider();
const maps = new OpenStreetMap();
const calendar = new Calendar();
const search = new Search();
const table = new Table();
const codex = new Codex();
const likes = new Likes();
const lightbox = new Lightbox();
const contract = new Contract();
const formModal = new FormModal();
