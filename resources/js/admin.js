import Alpine from 'alpinejs';
import villeAutocomplete from './ville-autocomplete.js';

window.Alpine = Alpine;

Alpine.data('villeAutocomplete', villeAutocomplete);

Alpine.start();
