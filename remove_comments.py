import os
import re

def remove_comments_from_str(text, extension):
    """Removes comments from a string based on the file extension."""
    if extension in ['.php', '.js', '.css']:
        # Remove multi-line comments /* ... */
        text = re.sub(re.compile(r'/\*.*?\*/', re.DOTALL), '', text)
        # Remove single-line comments // ... (ensure not part of a URL)
        # We look for // preceded by whitespace, start of line, or other non-colon chars
        text = re.sub(re.compile(r'(?<!:)\/\/.*'), '', text)
        if extension == '.php':
            # Remove PHP shell-style comments # ... 
            # (ensure not part of a string or hex color)
            # Safe approach for # in PHP: only if at start of line or preceded by spaces
            text = re.sub(re.compile(r'^\s*#.*', re.MULTILINE), '', text)
    elif extension in ['.html', '.php']:
        # Remove HTML comments <!-- ... -->
        text = re.sub(re.compile(r'<!--.*?-->', re.DOTALL), '', text)
    return text

def process_file(file_path):
    """Reads a file, removes comments, and writes it back."""
    ext = os.path.splitext(file_path)[1].lower()
    if ext not in ['.php', '.js', '.css', '.html']:
        return

    try:
        with open(file_path, 'r', encoding='utf-8') as f:
            content = f.read()
        
        cleaned_content = remove_comments_from_str(content, ext)
        
        # Remove empty lines that might have been left by comments
        # This is optional but keeps it clean
        # cleaned_content = "\n".join([line for line in cleaned_content.splitlines() if line.strip()])

        with open(file_path, 'w', encoding='utf-8') as f:
            f.write(cleaned_content)
        print(f"Cleaned: {file_path}")
    except Exception as e:
        print(f"Error processing {file_path}: {e}")

def main():
    root_dir = os.path.dirname(os.path.abspath(__file__))
    exclude_dirs = {'.git', '.gemini', 'node_modules', 'assets/vendor'}
    
    for root, dirs, files in os.walk(root_dir):
        # Exclude specific directories
        dirs[:] = [d for d in dirs if d not in exclude_dirs]
        
        for file in files:
            if file == 'remove_comments.py':
                continue
            
            file_path = os.path.join(root, file)
            process_file(file_path)

if __name__ == "__main__":
    confirm = input("Are you sure you want to remove all comments from the codebase? (y/n): ")
    if confirm.lower() == 'y':
        main()
        print("Done!")
    else:
        print("Operation cancelled.")
