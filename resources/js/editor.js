import { Editor, mergeAttributes } from '@tiptap/core'
import StarterKit from '@tiptap/starter-kit'
import { Heading } from '@tiptap/extension-heading'
import { TextAlign } from '@tiptap/extension-text-align'
import { TextStyle } from '@tiptap/extension-text-style'
import { Color } from '@tiptap/extension-color'
import { Highlight } from '@tiptap/extension-highlight'

// Prevent multiple setupEditor definitions
if (!window.setupEditor) {
  window.setupEditor = function (content) {
    let editor // Alpine's reactive engine automatically wraps component properties in proxy objects. 
    // If you attempt to use a proxied editor instance to apply a transaction, it will cause a 
    // "Range Error: Applying a mismatched transaction", so be sure to unwrap it using Alpine.raw(), 
    // or simply avoid storing your editor as a component property, as shown in this example.
    
    return {
      content: content,
      updatedAt: Date.now(), // Required for Alpine reactivity

      init(element) {
        const _this = this
        
        // Prevent duplicate editor initialization
        if (editor) {
          editor.destroy();
          editor = null;
        }
        
        editor = new Editor({
          element: element,
          extensions: [
            StarterKit.configure({
              heading: false,
              paragraph: {
                HTMLAttributes: {
                  class: 'mb-4 text-slate-200 leading-relaxed',
                },
              },
              bulletList: {
                HTMLAttributes: {
                  class: 'mb-4 text-slate-200',
                },
              },
              orderedList: {
                HTMLAttributes: {
                  class: 'mb-4 text-slate-200',
                },
              },
              listItem: {
                HTMLAttributes: {
                  class: 'text-slate-200',
                },
              },
              blockquote: {
                HTMLAttributes: {
                  class: 'border-l-4 border-slate-500 pl-4 italic text-slate-300 mb-4 bg-slate-800/50 py-2',
                },
              },
              code: {
                HTMLAttributes: {
                  class: 'bg-slate-700 text-amber-300 px-2 py-1 rounded text-sm font-mono',
                },
              },
              bold: {
                HTMLAttributes: {
                  class: 'font-bold text-white',
                },
              },
              italic: {
                HTMLAttributes: {
                  class: 'italic',
                },
              },
              strike: {
                HTMLAttributes: {
                  class: 'line-through text-slate-400',
                },
              },
              underline: {
                HTMLAttributes: {
                  class: 'underline',
                },
              },
            }),
            // Custom Heading extension with size-specific classes
            Heading.extend({
              levels: [1, 2, 3],
              renderHTML({ node, HTMLAttributes }) {
                const level = this.options.levels.includes(node.attrs.level)
                  ? node.attrs.level
                  : this.options.levels[0];
                const classes = {
                  1: 'text-3xl font-bold text-white mb-4 mt-6 first:mt-0',
                  2: 'text-2xl font-bold text-white mb-3 mt-5 first:mt-0',
                  3: 'text-xl font-bold text-white mb-3 mt-4 first:mt-0',
                };
                return [
                  `h${level}`,
                  mergeAttributes(this.options.HTMLAttributes, HTMLAttributes, {
                    class: classes[level],
                  }),
                  0,
                ];
              },
            }).configure({ levels: [1, 2, 3] }),
            TextAlign.configure({
              types: ['heading', 'paragraph'],
            }),
            TextStyle,
            Color,
            Highlight.configure({
              multicolor: true,
              HTMLAttributes: {
                class: 'bg-yellow-200 text-slate-900 px-1 rounded',
              },
            })
          ],
          content: this.content,
          editorProps: {
            attributes: {
              class: 'm-2 focus:outline-none'
            }
          },
          onCreate({ editor }) {
            _this.updatedAt = Date.now()
          },
          onUpdate({ editor }) {
            _this.content = editor.getHTML()
            _this.updatedAt = Date.now()
          },
          onSelectionUpdate({ editor }) {
            _this.updatedAt = Date.now()
          },
        })

        this.$watch('content', (content) => {
          // If the new content matches Tiptap's then we just skip.
          if (!editor || content === editor.getHTML()) return

          /*
            Otherwise, it means that an external source
            is modifying the data on this Alpine component,
            which could be Livewire itself.
            In this case, we only need to update Tiptap's
            content and we're done.
            For more information on the `setContent()` method, see:
              https://www.tiptap.dev/api/commands/set-content
          */
          editor.commands.setContent(content, false)
        })
      },

      // Helper methods for toolbar buttons
      isLoaded() {
        return editor
      },

      isActive(type, opts = {}) {
        return editor ? editor.isActive(type, opts) : false
      },

      toggleBold() {
        if (editor) {
          editor.chain().focus().toggleBold().run()
        }
      },

      toggleItalic() {
        if (editor) {
          editor.chain().focus().toggleItalic().run()
        }
      },

      toggleUnderline() {
        if (editor) {
          editor.chain().focus().toggleUnderline().run()
        }
      },

      toggleHeading(level) {
        if (editor) {
          editor.chain().focus().toggleHeading({ level }).run()
        }
      },

      toggleBulletList() {
        if (editor) {
          editor.chain().focus().toggleBulletList().run()
        }
      },

      toggleOrderedList() {
        if (editor) {
          editor.chain().focus().toggleOrderedList().run()
        }
      },

      toggleBlockquote() {
        if (editor) {
          editor.chain().focus().toggleBlockquote().run()
        }
      },

      toggleCode() {
        if (editor) {
          editor.chain().focus().toggleCode().run()
        }
      },

      setTextAlign(alignment) {
        if (editor) {
          editor.chain().focus().setTextAlign(alignment).run()
        }
      },

      destroy() {
        if (editor && !editor.isDestroyed) {
          editor.destroy()
          editor = null
        }
      }
    }
  }
}
