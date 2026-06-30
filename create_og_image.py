from PIL import Image

try:
    # Open original image
    img = Image.open('html/clasesGemma/public/logoGemma.png')
    
    # Convert to RGB (to remove alpha channel and get solid background)
    # If the image has transparency, paste it on a white background
    if img.mode in ('RGBA', 'LA') or (img.mode == 'P' and 'transparency' in img.info):
        background = Image.new('RGB', img.size, (255, 255, 255))
        background.paste(img, mask=img.split()[3]) # 3 is the alpha channel
        img = background
    else:
        img = img.convert('RGB')
        
    # Resize if too large (WhatsApp prefers < 300KB, recommended dims 1200x630 or square 800x800)
    # Let's resize it proportionally so max dimension is 800
    img.thumbnail((800, 800), Image.Resampling.LANCZOS)
    
    # Save as high quality JPG
    img.save('html/clasesGemma/public/og-image.jpg', 'JPEG', quality=85)
    print("Success: og-image.jpg created")
except Exception as e:
    print("Error:", e)
