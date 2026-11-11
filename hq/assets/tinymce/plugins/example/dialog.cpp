resp->write("<!DOCTYPE html>");
resp->write(" <html>");
resp->write(" <body>");
resp->write(" <h3>Custom dialog</h3>");
resp->write(" Input some text: <input id=\"content\">");
resp->write(" <button onclick=\"top.tinymce.activeEditor.windowManager.getWindows()[0].close();\">Close window</button>");
resp->write(" </body>");
resp->write(" </html>");
