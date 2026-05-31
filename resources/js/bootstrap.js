import axios from 'axios';
import html2canvas from 'html2canvas';
import 'flowbite';
import 'flowbite-datepicker';
import Datepicker from 'flowbite-datepicker/Datepicker';
import 'select2-tailwindcss-theme/dist/select2-tailwindcss-theme.min.css';

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

window.html2canvas = html2canvas; // <-- Make it available globally

window.Datepicker = Datepicker;
