import "./bootstrap";
import Alpine from "alpinejs";
import persist from "@alpinejs/persist";

// Only initialize Alpine if it hasn't been initialized yet
if (!window.Alpine) {
    Alpine.plugin(persist);
    window.Alpine = Alpine;
    Alpine.start();
}
