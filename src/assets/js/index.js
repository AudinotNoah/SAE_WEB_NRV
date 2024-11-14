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
        preferences = preferences.split(",").map(decodeURIComponent);
    }

    return preferences;
}
//version safe 
function setPreferences(preferences) {
    const PreferencesSafe = preferences.map(encodeURIComponent).join(',');

    document.cookie = `preferences=${PreferencesSafe}; path=/; max-age=31536000; Secure; SameSite=Strict`;
}

function switchPrefs(spectacleId) {
    let preferences = getPreferences();
    const button = document.getElementById('pref');

    // Vérifier si le spectacle est déjà dans les préférences
    if (preferences.includes(spectacleId.toString())) {
        // Retirer le spectacle des préférences
        preferences = preferences.filter(id => id !== spectacleId.toString());
        button.textContent = "Ajouter aux préférences";
    } else {
        // Ajouter le spectacle aux préférences
        preferences.push(spectacleId.toString());
        button.textContent = "Retirer des préférences";
    }

    setPreferences(preferences);
}
