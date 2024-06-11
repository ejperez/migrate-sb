window.addEventListener("DOMContentLoaded", () => {
	const form = document.getElementById("js-migrate-sb-form");

	if (!form) {
		return;
	}

	form.addEventListener("submit", async (event) => {
		event.preventDefault();

		const formData = new FormData(form);
		const postIds = formData.getAll("posts");
		const myHeaders = new Headers({
			"Content-Type": "application/json",
		});

		for (let i = 0; i < postIds.length; i++) {
			let request = await fetch(
				`/wp-json/migrate-sb/migrate/${postIds[i]}`,
				{
					method: "post",
					body: JSON.stringify({
						test_mode: formData.get("test_mode"),
					}),
					headers: myHeaders,
				}
			).catch((err) => console.error(err));

			let data = await request.text();
		}
	});
});
