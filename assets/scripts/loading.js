window.addEventListener("load", () => {
    let loader = document.getElementById("loader-wrapper")
    loader.classList.add("hidden")
    setTimeout(() => {
        loader.remove()
    }, 1500)
})