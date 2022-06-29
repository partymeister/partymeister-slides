import * as THREE from 'three';

export default {
    data: function () {
        return {
            camera: null,
            scene: null,
            animationFrameRequest: null,
            uniforms: {},
            container: null,
            fragmentShader: '',
        };
    },
    created() {
        this.camera = new THREE.OrthographicCamera(-1, 1, 1, -1, 0, 1);
        this.scene = new THREE.Scene();
        setTimeout(() => {
            this.container = document.querySelector('#shader-container');
        });

        window.addEventListener('resize', this.resize, false);

        this.resize();
    },
    methods: {
        resize() {
            if (this.renderer !== undefined && this.renderer !== null) {
                this.renderer.setSize(window.innerWidth, window.innerHeight);
            }

            if (this.uniforms["resolution"] !== undefined) {
                this.uniforms["resolution"].value = [window.innerWidth, window.innerHeight];
            }
        },
        loadScene() {
            if (this.fragmentShader === undefined || this.fragmentShader === '') {
                // console.log("NO SHADER - SKIPPING");
                return;
            }
            // console.log("LOAD SCENE");
            document.getElementById('shader-container').style.display = '';
            var geometry = new THREE.PlaneBufferGeometry(2, 2);

            this.uniforms = {
                "time": {value: 1.0},
                "resolution": {value: [window.innerWidth, window.innerHeight]}
            };

            var material = new THREE.ShaderMaterial({

                uniforms: this.uniforms,
                vertexShader: document.getElementById('vertexShader').textContent,
                fragmentShader: this.fragmentShader

            });

            var mesh = new THREE.Mesh(geometry, material);
            this.scene.add(mesh);

            this.renderer = new THREE.WebGLRenderer();
            // renderer.setPixelRatio(window.devicePixelRatio);
            this.container.appendChild(this.renderer.domElement);

            this.resize();
        },
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
        },
        animate(timestamp) {
            if (!this.fragmentShader) {
                return;
            }
            this.animationFrameRequest = requestAnimationFrame(this.animate);

            this.uniforms["time"].value = timestamp / 1000;

            this.renderer.render(this.scene, this.camera);
        }
    }
}
