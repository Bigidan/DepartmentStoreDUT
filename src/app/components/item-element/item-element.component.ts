import {Component, Input} from '@angular/core';
import {NgClass} from "@angular/common";
import {RouterLink} from "@angular/router";

@Component({
  selector: 'app-item-element',
  standalone: true,
    imports: [
        NgClass,
        RouterLink
    ],
  templateUrl: "item-element.component.html",
  styleUrl: 'item-element.component.css',
})
export class ItemElementComponent {
  @Input() item: any;
  @Input() selectedNavItem: any;

  get formattedValue(): string {
    return this.item.value.replaceAll('#', '<p>').replaceAll('@', '</p><p>');
  }
}
