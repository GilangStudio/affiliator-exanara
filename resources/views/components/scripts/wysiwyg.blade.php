<script src="{{ asset('libs/hugerte/hugerte.min.js') }}"></script>
<script>
      let options = {
        selector: "#editor",
        height: 300,
        menubar: false,
        statusbar: false,
        license_key: "gpl",
        // plugins: [
        //   "advlist",
        //   "autolink",
        //   "lists",
        //   "link",
        //   "image",
        //   "charmap",
        //   "preview",
        //   "anchor",
        //   "searchreplace",
        //   "visualblocks",
        //   "code",
        //   "fullscreen",
        //   "insertdatetime",
        //   "media",
        //   "table",
        //   "code",
        //   "help",
        //   "wordcount",
        // ],
        toolbar:
          "undo redo | styles | " +
          "bold italic backcolor | alignleft aligncenter " +
          "alignright alignjustify | bullist numlist outdent indent | " +
          "removeformat",
        content_style:
          "body { font-family: -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif; font-size: 14px; -webkit-font-smoothing: antialiased; }",
        setup: function (editor) {
          // Update hidden textarea when editor content changes
          editor.on('change', function () {
            const content = editor.getContent();
            const textarea = document.querySelector('textarea[name="content"]');
            if (textarea) {
              textarea.value = content;
              
              // Clear validation error if content is added
              if (content.trim() && content.trim() !== '<p></p>' && content.trim() !== '<p><br></p>') {
                textarea.classList.remove('is-invalid');
                const errorDiv = document.getElementById('content-error');
                if (errorDiv) {
                  errorDiv.style.display = 'none';
                }
              }
            }
          });
          
          // Also update on keyup for real-time sync
          editor.on('keyup', function () {
            const content = editor.getContent();
            const textarea = document.querySelector('textarea[name="content"]');
            if (textarea) {
              textarea.value = content;
            }
          });
        }
      };
      if (localStorage.getItem("tablerTheme") === "dark") {
        options.skin = "oxide-dark";
        options.content_css = "dark";
      }
</script>