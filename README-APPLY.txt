NEWS HUB - VISUAL REDESIGN PATCH

1. Giai nen vao thu muc goc repository Webtintuc (noi co thu muc upload/).
2. Cho phep ghi de cac file trung ten trong upload/.
3. Xoa hai file duoc liet ke trong REMOVE_OLD_FILES.txt.
4. Khong import lai SQL va khong sua database. Ten 8 thumbnail duoc giu nguyen.
5. Chay git diff --check, sau do git add/commit/push. GitHub Actions se deploy ma va anh.
6. Hard refresh trinh duyet bang Ctrl+F5.

Patch chi thay view, stylesheet va 8 anh minh hoa; khong thay Controller, Model, route, form action, CSRF hay schema.
