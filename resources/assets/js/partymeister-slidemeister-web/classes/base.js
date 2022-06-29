export default class Base {
    constructor() {
        this.uniforms = {
            "time": {value: 1.0},
            "resolution": {value: [window.innerWidth, window.innerHeight]}
        };

        this.camera = null;
        this.scene = null;
        this.animationFrameRequest = null;
        this.container = null;
        this.currentFrame = 0;

        setTimeout(() => {
            this.container = document.querySelector('#shader-container');
        });

        window.addEventListener('resize', this.resize.bind(this), false);

        this.resize();
    }

    resize() {
        if (this.renderer !== undefined && this.renderer !== null && this.sceneClass !== '') {
            this.camera.aspect = window.innerWidth / window.innerHeight;
            this.camera.updateProjectionMatrix();
            this.renderer.setSize(window.innerWidth, window.innerHeight);
        }

        if (this.uniforms && this.uniforms["resolution"] !== undefined) {
            this.uniforms["resolution"].value = [window.innerWidth, window.innerHeight];
        }
    }

    setUpScene() {
        document.getElementById('shader-container').style.display = '';

        this.loadScene();

        setTimeout(() => {
            this.container.appendChild(this.renderer.domElement);

            this.resize();
        }, 100);

    }

    loadScene() {
        // Stub
    }

    unloadScene() {
        // console.log("UNLOAD");
        document.getElementById('shader-container').style.display = 'none';
        window.cancelAnimationFrame(this.animationFrameRequest);
        // console.log("REMOVE CHILDREN");
        if (this.scene) {
            while (this.scene.children.length > 0) {
                this.scene.remove(this.scene.children[0]);
            }
        }

        var child = this.container.lastElementChild;

        // console.log("REMOVE DOMELEMENTS");
        while (child) {
            this.container.removeChild(child);
            child = this.container.lastElementChild;
        }

        this.renderer = null;
    }
}
