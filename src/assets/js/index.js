function getPreferences() {
    let cookies = document.cookie.split(';');
    let preferences = null;

    for (let i = 0; i < cookies.length; i++) {
        if (cookies[i].trim().startsWith("preferences=")) {
            preferences = cookies[i].trim().substring("preferences=".length);
            break;
        }
    }

    if (preferences === null || preferences === "" || preferences === ",") {
        preferences = [];  
    } else {
        preferences = preferences.split(",");
    }


    return preferences;
}

function setPreferences(preferences) {
    document.cookie = `preferences=${preferences.join(',')}; path=/; max-age=31536000`;
}

function switchPrefs(spectacleId) {
    let preferences = getPreferences();
    const button = document.getElementById('pref');

    if (preferences.includes(spectacleId.toString())) {

        let preftemp = [];

        for (let i = 0; i < preferences.length; i++) {
            if (preferences[i] !== spectacleId.toString()) {
                preftemp.push(preferences[i]);
            }
        }
        preferences = preftemp;
        button.textContent = "Ajouter aux préférences";
    } else {
        preferences.push(spectacleId.toString());
        button.textContent = "Retirer des préférences";
    }

    setPreferences(preferences); // faut update
}