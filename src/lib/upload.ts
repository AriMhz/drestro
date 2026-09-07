import sharp from "sharp";
import { writeFile, mkdir, unlink } from "fs/promises";
import path from "path";

/**
 * Saves a File object to the public/uploads directory as a WebP image.
 * @param file The File object from FormData
 * @param subfolder The subfolder inside public/uploads to save to
 * @returns The public URL of the saved image
 */
export async function processAndSaveImage(file: File, subfolder: string): Promise<string | null> {
  if (!file || file.size === 0 || !file.type.startsWith("image/")) {
    return null;
  }

  try {
    const bytes = await file.arrayBuffer();
    const buffer = Buffer.from(bytes);

    // Ensure the upload directory exists
    const uploadDir = path.join(process.cwd(), "public", "uploads", subfolder);
    await mkdir(uploadDir, { recursive: true });

    // Generate a unique filename
    const uniqueId = Math.random().toString(36).substring(2, 9) + "-" + Date.now();
    const filename = `${uniqueId}.webp`;
    const filepath = path.join(uploadDir, filename);

    // Process image with sharp and convert to webp
    await sharp(buffer)
      .webp({ quality: 80 }) // 80% quality is a good balance of size and clarity
      .toFile(filepath);

    // Return the public URL
    return `/uploads/${subfolder}/${filename}`;
  } catch (error) {
    console.error("Error processing image:", error);
    return null;
  }
}

/**
 * Deletes a file from the public directory given its public URL
 * @param imageUrl The public URL of the image (e.g. /uploads/hardware/123.webp)
 */
export async function deleteLocalImage(imageUrl: string) {
  if (!imageUrl || !imageUrl.startsWith("/uploads/")) return;

  try {
    const filepath = path.join(process.cwd(), "public", imageUrl);
    await unlink(filepath);
  } catch (error) {
    console.error("Error deleting image file:", error);
  }
}
