from PIL import Image
import sys

def make_transparent(input_path, output_path, tolerance=20):
    img = Image.open(input_path).convert("RGBA")
    data = img.getdata()

    new_data = []
    # Off-white color from the logo background
    target_r, target_g, target_b = 249, 248, 245
    # Pure white
    w_r, w_g, w_b = 255, 255, 255

    for item in data:
        r, g, b, a = item
        # If it's close to white or the off-white background, make it transparent
        if (abs(r - target_r) < tolerance and abs(g - target_g) < tolerance and abs(b - target_b) < tolerance) or \
           (abs(r - w_r) < tolerance and abs(g - w_g) < tolerance and abs(b - w_b) < tolerance):
            new_data.append((255, 255, 255, 0))
        else:
            new_data.append(item)

    img.putdata(new_data)
    img.save(output_path, "PNG")
    print(f"Saved {output_path}")

input_img = r"C:\Users\zouha\.gemini\antigravity\brain\71d13836-642b-4e47-8198-5ddae285657d\media__1777661214245.png"
output_img = r"c:\Users\zouha\OneDrive\Documents\MINI PROJETS\PROJET RECHERCHE SCI\frontend\public\aurem-logo.png"

make_transparent(input_img, output_img, tolerance=30)
