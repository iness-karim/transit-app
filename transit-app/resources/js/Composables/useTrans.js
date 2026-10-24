import { ref } from 'vue';
import en from '../Translations/en';
import fr from '../Translations/fr';

const translations = { en, fr };
const currentLocale = ref(localStorage.getItem('locale') || 'en');

export function useTrans() {
    const trans = (key) => {
        return translations[currentLocale.value][key] || key;
    };

    const setLocale = (locale) => {
        if (translations[locale]) {
            currentLocale.value = locale;
            localStorage.setItem('locale', locale);
        }
    };

    return {
        trans,
        setLocale,
        currentLocale
    };
}
