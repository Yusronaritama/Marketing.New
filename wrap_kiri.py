import sys

file_path = r'resources\views\mahasiswa\dashboard\progress\kiri\index.blade.php'

with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Add a vertical wrapper at the start (after the PHP block)
# The PHP block ends at @endphp

parts = content.split('@endphp\n')
if len(parts) == 2:
    new_content = parts[0] + '@endphp\n\n<div class="w-full flex flex-col gap-8">\n' + parts[1] + '\n</div>\n'
    with open(file_path, 'w', encoding='utf-8') as f:
        f.write(new_content)
    print('Added wrapper to kiri/index.blade.php')
else:
    print('Could not find @endphp')
