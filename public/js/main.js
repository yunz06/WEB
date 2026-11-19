document.addEventListener('submit', (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement)) return;

    const submitter = event.submitter;
    const confirmation = submitter instanceof HTMLElement && submitter.dataset.confirm
        ? submitter.dataset.confirm
        : form.dataset.confirm;

    if (confirmation && !window.confirm(confirmation)) {
        event.preventDefault();
    }
});
