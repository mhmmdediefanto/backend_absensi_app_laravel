import os
import cv2
from datetime import datetime

STATUS_COLORS = {
    "recognized": (0, 200, 0),   # Green
    "maybe": (0, 165, 255),      # Orange
    "unknown": (0, 0, 255),      # Red
}

STATUS_SHORT = {
    "recognized": "Rec",
    "maybe": "Maybe",
    "unknown": "Unknown",
}


def _format_label(face_data):
    folder_id = face_data.get("folder_id") or "Unknown"
    confidence = face_data.get("confidence", 0.0)
    status = face_data.get("status", "unknown")
    return [
        {"text": f"ID {folder_id}", "scale": 0.4},
        {"text": f"CONF {confidence:.1f}", "scale": 0.55},
        {"text": STATUS_SHORT.get(status, status), "scale": 0.35},
    ]


def _draw_label(image, x, y, w, h, segments):
    """Gambar label multi-baris di atas/bawah bbox"""
    font = cv2.FONT_HERSHEY_SIMPLEX
    thickness = 1
    padding = 6
    margin = 6
    min_scale = 0.3

    max_width = image.shape[1] - margin * 2

    def measure(scales):
        sizes = []
        for seg, scale in zip(segments, scales):
            metrics = cv2.getTextSize(seg["text"], font, scale, thickness)
            sizes.append(metrics)
        width = max(size[0][0] for size in sizes)
        height = sum(size[0][1] + size[1] for size in sizes) + (len(sizes) - 1) * 4
        return sizes, width, height

    base_scales = [max(seg["scale"], min_scale) for seg in segments]
    sizes, text_width, text_height = measure(base_scales)

    available_width = max_width - padding * 2
    if text_width > available_width and available_width > 0:
        shrink_ratio = available_width / text_width
        adjusted_scales = [
            max(min_scale, scale * shrink_ratio) for scale in base_scales
        ]
        sizes, text_width, text_height = measure(adjusted_scales)
        base_scales = adjusted_scales

    box_width = min(text_width + padding * 2, max_width)
    box_height = text_height + padding * 2

    top_left_x = max(margin, min(x, image.shape[1] - box_width - margin))
    top_left_y = y - box_height - 2

    if top_left_y < margin:
        top_left_y = y + h + 2
        if top_left_y + box_height > image.shape[0] - margin:
            top_left_y = image.shape[0] - box_height - margin

    cv2.rectangle(
        image,
        (top_left_x, top_left_y),
        (top_left_x + box_width, top_left_y + box_height),
        (255, 255, 255),
        thickness=-1,
    )
    cv2.rectangle(
        image,
        (top_left_x, top_left_y),
        (top_left_x + box_width, top_left_y + box_height),
        (0, 0, 0),
        thickness=1,
    )

    cursor_y = top_left_y + padding
    for idx, seg in enumerate(segments):
        (line_width, line_height), baseline = sizes[idx]
        cursor_y += line_height
        cv2.putText(
            image,
            seg["text"],
            (top_left_x + padding, cursor_y),
            font,
            base_scales[idx],
            (0, 0, 0),
            thickness,
            cv2.LINE_AA,
        )
        cursor_y += baseline + 4


def visualize_recognition(image_path, recognition_data, output_path, metadata=None):
    """
    Render hasil face recognition dengan bounding box + label.

    Args:
        image_path (str): path gambar sumber
        recognition_data (list[dict]): list data wajah dari FaceRecognizer
        output_path (str): file tujuan (akan disimpan sebagai .jpg/.png)
        metadata (dict): info tambahan untuk ditampilkan (opsional)
    """
    image = cv2.imread(image_path)
    if image is None:
        return False

    for face in recognition_data:
        position = face.get("position") or {}
        x = int(position.get("x", 0))
        y = int(position.get("y", 0))
        w = int(position.get("width", 0))
        h = int(position.get("height", 0))
        status = face.get("status", "unknown")
        color = STATUS_COLORS.get(status, (255, 255, 255))

        cv2.rectangle(image, (x, y), (x + w, y + h), color, 2)
        label_lines = _format_label(face)
        _draw_label(image, x, y, w, h, label_lines)

    if metadata:
        bottom_text = metadata.get("category")
        if bottom_text:
            info = f"{bottom_text} | {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}"
            cv2.putText(
                image,
                info,
                (10, image.shape[0] - 10),
                cv2.FONT_HERSHEY_SIMPLEX,
                0.5,
                (255, 255, 255),
                1,
                cv2.LINE_AA,
            )

    os.makedirs(os.path.dirname(output_path), exist_ok=True)
    cv2.imwrite(output_path, image)
    return True

