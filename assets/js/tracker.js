// this "tracks" the changes in the form and updates the preview

$(function () {
  const $form = $("#Form");

  // render preview on load
  // this only can happen if not finished yet
  $form.request("onPreview", {
    data: $form.serialize(), // serialize form data properly
    success: function (response) {
      $("#preview").html(response["result"]);
    },
  });

  // debounce to avoid too many AJAX calls
  let debounceTimer;

  $form.on("input change", ":input", function (event) {
    clearTimeout(debounceTimer);

    $("#preview").addClass("loading");

    debounceTimer = setTimeout(function () {
      // Trigger the backend handler `onPreview`
      $form.request("onPreview", {
        data: $form.serialize(), // serialize form data properly
        beforeSend: function () {},
        success: function (response) {
          $("#preview").html(response["result"]);
          $("#preview").removeClass("loading");
        },
      });
    }, 500); // wait after last input before sending
  });

  if ("MutationObserver" in window && $form.length) {
    let isReloading = false;

    let mutationDebounceTimer;

    const obs = new MutationObserver((mutations) => {
      for (const mutation of mutations) {
        if (mutation.addedNodes.length || mutation.removedNodes.length) {
          clearTimeout(mutationDebounceTimer);
          
          mutationDebounceTimer = setTimeout(() => {
            if (isReloading) return;

            isReloading = true;
            $form.request("onPreview", {
              // ← must exist in your controller
              data: $form.serialize(),
              complete: function () {
                console.log("Form reloaded after mutation.");
                isReloading = false;
              },
              success: function (response) {
                console.log('Preview updated');
                $("#preview").html(response["result"]);
              },
            });
          }, 500);
          break;
        }
      }
    });

    obs.observe($form[0], { attributes: true, childList: true, subtree: true });
  }
});
