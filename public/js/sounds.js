const AppSounds = {
    enabled: localStorage.getItem('sounds_enabled') !== '0',

    files: {
        correct: new Audio('/sounds/correct.mp3'),
        wrong: new Audio('/sounds/wrong.mp3'),
        click: new Audio('/sounds/click.mp3'),
        reward: new Audio('/sounds/reward.mp3'),
        chest: new Audio('/sounds/chest.mp3'),
        finish: new Audio('/sounds/finish.mp3'),
    },

    play(name) {
        if (!this.enabled || !this.files[name]) return;

        const sound = this.files[name];
        sound.currentTime = 0;
        sound.volume = 0.45;
        sound.play().catch(() => {});
    },

    toggle() {
        this.enabled = !this.enabled;
        localStorage.setItem('sounds_enabled', this.enabled ? '1' : '0');
        return this.enabled;
    }
};

document.addEventListener('click', function (e) {
    if (
        e.target.closest('.btn') ||
        e.target.closest('.mainMenu__link') ||
        e.target.closest('.miniBtn')
    ) {
        AppSounds.play('click');
    }
});