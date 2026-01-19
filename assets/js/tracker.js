// this is not just the tracker, naming colud be better
$(function () {
  const $form = $("#Form");

  // render preview on load
  // this only can happen if not finished yet
  $form.request("onPreview", {
    data: $form.serialize(), // serialize form data properly
    // NOK?
    // update: { preview_test: '#preview' },
    success: function (response) {
      // console.log('Preview updated');
      $("#preview").html(response["result"]);
      // $('#preview iframe').attr('srcdoc', response['result']);
    },
  });

  // debounce to avoid too many AJAX calls
  let debounceTimer;

  $form.on("input change", ":input", function (event) {
    clearTimeout(debounceTimer);

    // $('#preview').html('loading');
    $("#preview").addClass("loading");

    debounceTimer = setTimeout(function () {
      // console.log('Form input changed:', event.target.name);
      // console.log('value:', event.target.value);

      // could one just use $form.serialize() and vue / react?
      // console.log('$form.serializeObject(): ', $form.serializeObject());
      // console.log('$form.serialize(): ', $form.serialize());

      // Trigger the backend handler `onPreview`
      $form.request("onPreview", {
        data: $form.serialize(), // serialize form data properly
        // NOK?
        // update: { preview_test: '#preview' },
        beforeSend: function () {},
        success: function (response) {
          // console.log('Preview updated');
          $("#preview").html(response["result"]);
          // $('#preview').attr('srcdoc', response['result']);
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
        console.log('Mutation detected:', mutation);
        // if (mutation.type === 'attributes' || mutation.addedNodes.length || mutation.removedNodes.length) {
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
                // $('#preview iframe').attr('srcdoc', response['result']);
              },
            });
          }, 500);
          break;
        }
      }
    });

    obs.observe($form[0], { attributes: true, childList: true, subtree: true });
  }

  // testing
  // looks OK - how to do it correctly?
  // it needs to be done after the editor has been added?
  /*
    // Register the icon
    $.FroalaEditor.DefineIcon('insertName', { NAME: 'user' });

    // Register the command
    $.FroalaEditor.RegisterCommand('insertName', {
        title: 'Insert Name',
        icon: 'insertName',
        callback: function() {
            this.html.insert('{name}');
        }
    });
    */
});
