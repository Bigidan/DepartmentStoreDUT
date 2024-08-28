import {Component, ElementRef, EventEmitter, Output, ViewChild} from '@angular/core';
import { MatIconModule } from "@angular/material/icon";
import {CardWindowComponent} from "../card-window/card-window.component";

@Component({
  selector: 'app-side-nav',
  standalone: true,
  imports: [
    MatIconModule,
    CardWindowComponent
  ],
  templateUrl: "side-nav.component.html",
  styleUrls: ['side-nav.component.css'],
})
export class SideNavComponent {
  @Output() requestOpenCard = new EventEmitter<string>();
  openCard(commit: string) {
    this.requestOpenCard.emit(commit);
  }

  @Output() navItemSelected = new EventEmitter<string>();

  constructor(private el: ElementRef) {}

  ngOnInit() {
    const savedItem = localStorage.getItem('activeNavItem') || 'storage';
    const ul = this.el.nativeElement.querySelector('ul');
    const activeLi = ul.querySelector(`li[data-item=${savedItem}]`);
    if (activeLi) {
      activeLi.classList.add('active');
    }
    this.navItemSelected.emit(savedItem);
  }

  selectNavItem(event: Event, item: string) {
    const ul = this.el.nativeElement.querySelector('ul');
    const lis = ul.querySelectorAll('li');
    lis.forEach((li: HTMLElement) => li.classList.remove('active'));
    const target = event.currentTarget as HTMLElement;
    target.classList.add('active');
    localStorage.setItem('activeNavItem', item);
    this.navItemSelected.emit(item);
  }
}
