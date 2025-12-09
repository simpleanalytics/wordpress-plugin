import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";

/** @type {import('tailwindcss').Config} */
module.exports = {
    content: ["./**/*.php"],
    important: ".sa-settings",
    theme: {
        extend: {
            colors: {
                primary: "#FF4F64",
                primaryBg: "#eef9ff",
                littleMuted: "#68797b",
            },
            fontFamily: {
                sans: ["Space Grotesk", ...defaultTheme.fontFamily.sans],
            },
            borderWidth: {
                3: "3px",
            },
        },
    },
    plugins: [forms()],
};
